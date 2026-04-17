const fs = require('fs-extra');
const path = require('path');
const { execSync } = require('child_process');

const devRoot = path.resolve(__dirname, '../../');
const buildRoot = path.resolve(__dirname, '../../../build/cookie-law-info');
const buildAdmin = path.join(buildRoot, 'lite/admin');
const distDev = path.join(buildAdmin, 'dist-dev');
const distProd = path.join(buildAdmin, 'dist-prod');
const distFinal = path.join(buildAdmin, 'dist');

// Use vue-cli-service from the local dev env
const vueCli = path.resolve(__dirname, 'node_modules/.bin/vue-cli-service');

async function build() {
  try {
    console.log('📦 Copying plugin to build directory...');

    const filter = (src) => {
      const rel = path.relative(devRoot, src);
      if (rel === 'scripts' || rel.startsWith(`scripts${path.sep}`)) {
        return false;
      }
      return (
        !/node_modules/.test(src) &&
        !/dist(-dev|-prod)?/.test(src) &&
        path.basename(src) !== 'bitbucket-pipelines.yml'
      );
    };

    await fs.copy(devRoot, buildRoot, { filter });
    console.log('✅ Copied to:', buildRoot);

    console.log('🔨 Building development (expanded)...');
    execSync(`${vueCli} build --mode development --dest "${distDev}"`, {
      cwd: __dirname,
      stdio: 'inherit',
    });

    console.log('🔨 Building production (minified)...');
    execSync(`${vueCli} build --mode production --dest "${distProd}"`, {
      cwd: __dirname,
      stdio: 'inherit',
    });

    console.log('🔨 Running webpack-lite build...');
    execSync('yarn run build-webpack-lite', {
      cwd: __dirname,
      stdio: 'inherit',
    });

    console.log('🔗 Merging all dist folders...');
    await fs.emptyDir(distFinal);

    for (const src of [distDev, distProd]) {
      if (await fs.pathExists(src)) {
        await fs.copy(src, distFinal, { overwrite: true });
        console.log(`✔️  Merged from: ${src}`);
      } else {
        console.warn(`⚠️  Skipped missing: ${src}`);
      }
    }

    console.log('🧹 Cleaning up...');
    await fs.remove(path.join(buildAdmin, 'node_modules'));
    await fs.remove(path.join(buildAdmin, 'src'));
    await fs.remove(path.join(buildRoot, '.git'));
    await fs.remove(path.join(buildRoot, '.gitignore'));
    await fs.remove(distDev);
    await fs.remove(distProd);

    console.log('🎉 Final build completed at:', distFinal);
  } catch (err) {
    console.error('❌ Build failed:', err);
  }
}

build();