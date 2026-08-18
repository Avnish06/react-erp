const fs = require('fs');
const Client = require('ssh2-sftp-client');
const { Client: SSHClient } = require('ssh2');

const config = {
  host: '161.248.37.138',
  port: 5726,
  username: 'root',
  password: 'R8lD59Uc0hqRFC'
};

async function uploadAndDeploy() {
  const sftp = new Client();
  try {
    console.log('Connecting via SFTP...');
    await sftp.connect(config);
    
    console.log('Uploading server_deploy.zip...');
    await sftp.put('server_deploy.zip', '/root/server_deploy.zip');
    
    console.log('Upload complete. Disconnecting SFTP...');
    await sftp.end();

    console.log('Connecting via SSH to extract and deploy...');
    const conn = new SSHClient();
    conn.on('ready', () => {
      console.log('SSH connection ready.');
      const script = `
        cd /root
        mkdir -p erp_project
        unzip -o server_deploy.zip -d erp_project
        cd erp_project
        npm install --production
        pm2 restart all || node index.js &
      `;
      conn.exec(script, (err, stream) => {
        if (err) throw err;
        stream.on('close', (code, signal) => {
          console.log('Stream :: close :: code: ' + code + ', signal: ' + signal);
          conn.end();
        }).on('data', (data) => {
          console.log('STDOUT: ' + data);
        }).stderr.on('data', (data) => {
          console.log('STDERR: ' + data);
        });
      });
    }).connect(config);

  } catch (err) {
    console.error('Error:', err);
  }
}

uploadAndDeploy();
