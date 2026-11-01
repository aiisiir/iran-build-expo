// Copies the built Jodit editor from node_modules into public/vendor/jodit (self-hosted: no CDN).
// Usage: npm install && npm run editor:install   (then commit public/vendor/jodit)
import { cpSync, existsSync, mkdirSync } from 'node:fs';

const source = 'node_modules/jodit/es2021';
const target = 'public/vendor/jodit';

if (!existsSync(source)) {
    console.error('Run "npm install" first.');
    process.exit(1);
}

mkdirSync(target, { recursive: true });
const script = existsSync(`${source}/jodit.fat.min.js`) ? 'jodit.fat.min.js' : 'jodit.min.js'; // fat = all plugins in one file
cpSync(`${source}/${script}`, `${target}/jodit.min.js`);
cpSync(`${source}/jodit.min.css`, `${target}/jodit.min.css`);
cpSync('node_modules/jodit/LICENSE.txt', `${target}/LICENSE.txt`);
if (script === 'jodit.min.js' && existsSync(`${source}/plugins`)) {
    cpSync(`${source}/plugins`, `${target}/plugins`, { recursive: true });
}
console.log(`Jodit copied to ${target} (${script}).`);
