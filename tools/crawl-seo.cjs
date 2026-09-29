const { execFileSync } = require('node:child_process');
const { JSDOM } = require('jsdom');
const origin = process.argv.includes('--origin');
const read = url => {
  if (new URL(url).hostname !== 'cvipi.info') throw Error('Unexpected sitemap host');
  const options = ['-sS', '--max-time', '20', '-w', '\n%{http_code}', url];
  if (origin) options.unshift('--resolve', 'cvipi.info:443:50.6.154.170');
  const result = execFileSync('curl', options, {encoding:'utf8'});
  const boundary = result.lastIndexOf('\n');
  return {status:Number(result.slice(boundary+1)), body:result.slice(0,boundary)};
};
const xmlUrls = url => {
  const response = read(url);
  if (response.status !== 200) throw Error(url + ': HTTP ' + response.status);
  const dom = new JSDOM(response.body, {contentType:'application/xml'});
  const urls = [...dom.window.document.querySelectorAll('loc')].map(node=>node.textContent);
  dom.window.close(); return urls;
};
const sitemaps = xmlUrls('https://cvipi.info/wp-sitemap.xml');
const urls = sitemaps.flatMap(xmlUrls);
if (new Set(urls).size !== urls.length) throw Error('Duplicate sitemap URLs');
const results = [];
for (const url of urls) {
  const response = read(url);
  const dom = new JSDOM(response.body);
  const document = dom.window.document;
  const schema = [...document.querySelectorAll('script[type="application/ld+json"]')].flatMap(node=>JSON.parse(node.textContent)['@graph'] || []);
  const canonical = document.querySelector('link[rel="canonical"]')?.getAttribute('href');
  const robots = document.querySelector('meta[name="robots"]')?.content || '';
  const checks = {status:response.status, title:document.title, description:document.querySelector('meta[name="description"]')?.content, canonical, canonicalCount:document.querySelectorAll('link[rel="canonical"]').length, h1:document.querySelectorAll('h1').length, schema: schema.map(item=>item['@type']), indexable:!robots.includes('noindex')};
  if (response.status !== 200 || !checks.title || !checks.description || canonical !== url || checks.canonicalCount !== 1 || checks.h1 !== 1 || !checks.indexable || !schema.length) throw Error(JSON.stringify({url,...checks}));
  results.push({url,...checks}); dom.window.close();
}
console.log(JSON.stringify({source:origin?'origin':'public CDN',sitemaps,pages:results},null,2));
