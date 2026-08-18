const fs = require('fs');
const path = require('path');

const directoryPath = path.join(__dirname, 'src', 'components');

const replacements = [
  // Primary highlight colors
  { regex: /bg-blue-/g, replace: 'bg-orange-' },
  { regex: /bg-indigo-/g, replace: 'bg-orange-' },
  { regex: /text-blue-(?!9\d\d)/g, replace: 'text-orange-' }, // Ignore text-blue-900/950 we add
  { regex: /text-indigo-/g, replace: 'text-orange-' },
  { regex: /border-blue-/g, replace: 'border-orange-' },
  { regex: /border-indigo-/g, replace: 'border-orange-' },
  { regex: /ring-blue-/g, replace: 'ring-orange-' },
  { regex: /ring-indigo-/g, replace: 'ring-orange-' },

  // Dark text / background mapping to deep blue
  { regex: /text-gray-900/g, replace: 'text-blue-950' },
  { regex: /text-gray-800/g, replace: 'text-blue-900' },
  { regex: /bg-gray-900/g, replace: 'bg-blue-950' },
  { regex: /bg-gray-800/g, replace: 'bg-blue-900' },
  { regex: /bg-gray-950/g, replace: 'bg-blue-950' },

  // Secondary text mapping to slate (cooler gray to match blue)
  { regex: /text-gray-600/g, replace: 'text-slate-600' },
  { regex: /text-gray-500/g, replace: 'text-slate-500' }
];

function processDirectory(dir) {
  const files = fs.readdirSync(dir);

  files.forEach((file) => {
    const fullPath = path.join(dir, file);
    if (fs.statSync(fullPath).isDirectory()) {
      processDirectory(fullPath);
    } else if (fullPath.endsWith('.jsx')) {
      let content = fs.readFileSync(fullPath, 'utf8');
      let updatedContent = content;

      replacements.forEach(({ regex, replace }) => {
        updatedContent = updatedContent.replace(regex, replace);
      });

      if (content !== updatedContent) {
        fs.writeFileSync(fullPath, updatedContent, 'utf8');
        console.log(`Updated ${file}`);
      }
    }
  });
}

processDirectory(directoryPath);
console.log('Theme update complete.');
