#!/usr/bin/env node
const { initDb } = require('../src/database');
const { applySchema } = require('../src/database/schema');

async function run() {
  console.log('[Nova] Initializing database migration...');
  try {
    const db = await initDb();
    await applySchema(db);
    console.log('✅ [Nova] Database migration and seed completed successfully!');
    process.exit(0);
  } catch (err) {
    console.error('❌ [Nova] Migration failed:', err);
    process.exit(1);
  }
}

run();
