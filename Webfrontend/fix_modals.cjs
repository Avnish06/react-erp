const fs = require('fs');
const path = require('path');
const dir = 'src/components';
let modifiedFiles = 0;

function processFile(filePath) {
  let content = fs.readFileSync(filePath, 'utf8');
  let originalContent = content;

  // Regex to find modal inner containers and add max-h-[90vh] flex flex-col
  const innerContainerRegex = /(className="[^">]*?w-full max-w-[^">]*?)(overflow-hidden)([^">]*?")/g;
  
  content = content.replace(innerContainerRegex, (match, p1, p2, p3) => {
    if (match.includes('max-h-[')) return match; // already has max-h
    return p1 + 'max-h-[90vh] flex flex-col ' + p2 + p3;
  });

  // Now find forms inside these modals to add overflow-y-auto flex-1
  // We'll target forms with className containing "p-6" or "p-4" or "p-8" and "space-y-"
  const formRegex = /(<form[^>]*className="[^">]*?p-\d[^">]*?)(")/g;
  content = content.replace(formRegex, (match, p1, p2) => {
    if (match.includes('overflow-y-auto') || match.includes('flex-1')) return match;
    return p1 + ' overflow-y-auto flex-1' + p2;
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
