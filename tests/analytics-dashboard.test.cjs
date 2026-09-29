const test = require('node:test');
const assert = require('node:assert/strict');
const { duration, average, comparison, csvCell, calendarDays } = require('../assets/admin/analytics-dashboard.js');
test('durations and denominators', () => {
  assert.equal(duration(119.8), '2m 0s');
  assert.equal(average(400, 8), 50);
  assert.equal(average(0, 0), 0);
});
test('zero baselines and partial days never invent percentage growth', () => {
  assert.equal(comparison(4, 0, false), 'No previous activity');
  assert.equal(comparison(0, 0, false), 'No activity in either period');
  assert.equal(comparison(15, 10, false), '+50.0% vs. previous period');
  assert.equal(comparison(3, 10, true), 'Yesterday: 10 (full day)');
});
test('dates follow the GA property timezone, including DST', () => {
  const data = { range: '7', timezone: 'America/Los_Angeles', fetchedAt: '2026-03-09T01:00:00Z' };
  assert.deepEqual(calendarDays(data), ['2026-03-01', '2026-03-02', '2026-03-03', '2026-03-04', '2026-03-05', '2026-03-06', '2026-03-07']);
  assert.deepEqual(calendarDays({ ...data, range: 'today' }), ['2026-03-08']);
  assert.equal(calendarDays({ ...data, range: '90' }).length, 90);
});
test('CSV neutralizes formulas and quotes values', () => {
  assert.equal(csvCell('=HYPERLINK("bad")'), '"\'=HYPERLINK(""bad"")"');
  assert.equal(csvCell(' +1'), '"\' +1"');
  assert.equal(csvCell('a,b'), '"a,b"');
  assert.equal(csvCell(12), '"12"');
});
