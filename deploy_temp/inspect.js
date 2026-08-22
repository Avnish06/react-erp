const { Client } = require('ssh2');

const conn = new Client();
conn.on('ready', () => {
  console.log('Client :: ready');
  conn.exec(`
    echo "=== PM2 Info ==="
    pm2 describe react-erp | grep "pm_cwd"
    pm2 describe react-erp-backend | grep "pm_cwd"
  `, (err, stream) => {
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
}).connect({
  host: '161.248.37.138',
  port: 5726,
  username: 'root',
  password: 'R8lD59Uc0hqRFC'
});
