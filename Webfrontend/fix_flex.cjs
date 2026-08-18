const fs = require('fs');
const path = require('path');
const dir = 'src/components';
let modifiedFiles = 0;

function processFile(filePath) {
  let content = fs.readFileSync(filePath, 'utf8');
  let originalContent = content;

  // Find className strings containing md:flex-row but missing flex-wrap
  const regex = /className="([^"]*md:flex-row[^"]*)"/g;
  content = content.replace(regex, (match, classes) => {
    if (classes.includes('flex-wrap') || classes.includes('flex-nowrap')) return match;
    return `className="${classes} flex-wrap"`;
  });

  if (content !== originalContent) {
    fs.writeFileSync(filePath, content);
    modifiedFiles++;
    console.log('Modified', filePath);
  }
}

fs.readdirSync(dir).forEach(file => {
  if (file.endsWith('.jsx')) {
    processFile(path.join(dir, file));
  }
});
console.log('Total files modified:', modifiedFiles);
