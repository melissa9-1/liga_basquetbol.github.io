import test from 'node:test';
import assert from 'node:assert/strict';

import { createApp } from '../src/app.js';

test('createApp debe devolver la aplicación express', () => {
  const app = createApp();
  assert.ok(app);
  assert.equal(typeof app.listen, 'function');
});
