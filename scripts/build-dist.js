import fs from 'node:fs';
import path from 'node:path';

const rootDir = process.cwd();
const distDir = path.join(rootDir, 'dist');
const publicDir = path.join(rootDir, 'public');
const publicBuildDir = path.join(rootDir, 'public', 'build');

try {
  // 1. Ensure dist directory exists
  if (!fs.existsSync(distDir)) {
    fs.mkdirSync(distDir, { recursive: true });
  }

  // 2. Copy compiled assets from public/build into dist
  if (fs.existsSync(publicBuildDir)) {
    fs.cpSync(publicBuildDir, distDir, { recursive: true });
  }

  // 3. Copy public files into dist
  if (fs.existsSync(publicDir)) {
    fs.cpSync(publicDir, distDir, {
      recursive: true,
      filter: (src) => !src.endsWith(path.join('public', 'build')) && !src.includes(path.join('public', 'build', path.sep)),
    });
  }

  // 4. Ensure index.html exists in dist for static/SPA deployment validators
  const distIndexHtml = path.join(distDir, 'index.html');
  if (!fs.existsSync(distIndexHtml)) {
    const offlineHtmlPath = path.join(publicDir, 'offline.html');
    if (fs.existsSync(offlineHtmlPath)) {
      fs.copyFileSync(offlineHtmlPath, distIndexHtml);
    } else {
      fs.writeFileSync(
        distIndexHtml,
        `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TPMS App</title>
</head>
<body>
    <div id="app"></div>
</body>
</html>`
      );
    }
  }

  console.log('✅ [Build Step] Successfully generated dist/ directory from public assets.');
} catch (err) {
  console.error('⚠️ [Build Step] Error generating dist/ directory:', err);
}
