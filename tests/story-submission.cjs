const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(`${__dirname}/../assets/js/modules/StorySubmission.js`, 'utf8').replace('export default StorySubmission;', 'globalThis.StorySubmission = StorySubmission;');
const context = { FormData, URLSearchParams, CustomEvent: class {}, window: { grecaptcha: { getResponse: () => 'local-test-token' } }, document: { dispatchEvent() {} } };
vm.runInNewContext(source, context);
const proto = context.StorySubmission.prototype;
const nodes = new Map();
const node = selector => {
  if (!nodes.has(selector)) nodes.set(selector, { textContent: '', hidden: false, setAttribute() {}, removeAttribute() {}, focus() {} });
  return nodes.get(selector);
};
const attachment = { files: [], dataset: { maxBytes: String(5 * 1024 * 1024) }, setCustomValidity(value) { this.validityMessage = value; }, setAttribute() {}, removeAttribute() {} };
const form = {
  elements: { submission_type: { value: 'content' }, title: { value: 'Keep title' }, story: { value: 'Keep body', removeAttribute() {} }, attachment },
  reportValidity: () => true, querySelectorAll: () => [], setAttribute() {}, removeAttribute() {}, reset() {},
};
const subject = Object.create(proto);
subject.form = form;
subject.dialog = { querySelector: node };
subject.updateType(true);
assert.equal(node('[data-story-title-label]').textContent, 'Content title');
assert.equal(node('[data-story-body-label]').textContent, 'Your content');
assert.equal(node('[data-story-submit-label]').textContent, 'Submit Content');
form.elements.submission_type.value = 'story';
subject.updateType(true);
assert.equal(node('[data-story-title-label]').textContent, 'Story title');
assert.equal(node('[data-story-body-label]').textContent, 'Your story');
assert.equal(form.elements.title.value, 'Keep title');
assert.equal(form.elements.story.value, 'Keep body');
assert(subject.validateAttachment());
attachment.files = [{ name: 'test.docm', size: 100 }];
assert(!subject.validateAttachment(), 'Reject macro-enabled extension in the browser');
assert(!node('[data-story-remove-attachment]').hidden, 'Selected file can be removed');
attachment.files = [{ name: 'large.pdf', size: 6 * 1024 * 1024 }];
assert(!subject.validateAttachment(), 'Reject an oversized file in the browser');
attachment.files = [{ name: 'valid.DOCX', size: 100 }];
assert(subject.validateAttachment(), 'Case-insensitive Word extension supported');
attachment.files = [];
assert(subject.validateAttachment());
assert(node('[data-story-remove-attachment]').hidden);
assert.equal(attachment.validityMessage, '');

let capturedBody;
context.fetch = async (url, options) => { capturedBody = options.body; return { json: async () => ({ success: true, data: {} }) }; };
subject.dialog.dataset = { endpoint: '/local-test' };
(async () => {
  const data = new FormData();
  data.set('attachment', new Blob(['Private local QA']), 'test.txt');
  await subject.request(data);
  assert(capturedBody instanceof FormData, 'Upload request must remain multipart FormData');
  assert.equal(await capturedBody.get('attachment').text(), 'Private local QA', 'File bytes are not converted to a string');
  await subject.request({ action: 'session' });
  assert(capturedBody instanceof URLSearchParams, 'Session requests retain their existing encoding');
  console.log('Dynamic labels, preserved inputs, file selection/removal/validation, and multipart transport checks passed.');
})().catch(error => { console.error(error); process.exitCode = 1; });
