#!/usr/bin/env node
const readline = require('readline');
const bcrypt = require('bcryptjs');
const { initDb } = require('../src/database');
const { applySchema } = require('../src/database/schema');

function askQuestion(query) {
  const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout,
  });
  return new Promise((resolve) =>
    rl.question(query, (ans) => {
      rl.close();
      resolve(ans.trim());
    })
  );
}

async function run() {
  console.log('==============================================');
  console.log('         Nova User Creation Utility           ');
  console.log('==============================================');

  const db = await initDb();
  await applySchema(db);

  const args = process.argv.slice(2);
  let email = '';
  let username = '';
  let firstName = '';
  let lastName = '';
  let password = '';
  let isAdmin = 1;
  let nonInteractive = args.includes('--non-interactive') || args.includes('-y') || args.includes('--default');

  for (let i = 0; i < args.length; i++) {
    if (args[i] === '--email' && args[i + 1]) email = args[++i];
    if (args[i] === '--username' && args[i + 1]) username = args[++i];
    if (args[i] === '--first' && args[i + 1]) firstName = args[++i];
    if (args[i] === '--last' && args[i + 1]) lastName = args[++i];
    if (args[i] === '--password' && args[i + 1]) password = args[++i];
    if (args[i] === '--admin') isAdmin = 1;
    if (args[i] === '--user') isAdmin = 0;
  }

  // If arguments provided or nonInteractive, don't ask
  const hasArgs = email || username || password || nonInteractive;

  if (!hasArgs && process.stdin.isTTY) {
    email = await askQuestion('Enter Email Address: ');
    username = await askQuestion('Enter Username: ');
    firstName = await askQuestion('Enter First Name: ');
    lastName = await askQuestion('Enter Last Name: ');
    password = await askQuestion('Enter Password: ');
    const adminAns = await askQuestion('Is this user an Administrator? (yes/no) [yes]: ');
    isAdmin = adminAns.toLowerCase().startsWith('n') ? 0 : 1;
  }

  // Fallbacks if blank
  if (!email) email = 'admin@nova.test';
  if (!username) username = 'admin';
  if (!firstName) firstName = 'Nova';
  if (!lastName) lastName = 'Administrator';
  if (!password) password = 'NovaAdmin123!';

  const hashedPassword = await bcrypt.hash(password, 10);

  const existing = await db.get('SELECT id FROM users WHERE email = ? OR username = ?', [email, username]);
  if (existing) {
    await db.query(
      `UPDATE users SET first_name = ?, last_name = ?, email = ?, password = ?, root_admin = ?, status = 'active' WHERE id = ?`,
      [firstName, lastName, email, hashedPassword, isAdmin, existing.id]
    );
    console.log('----------------------------------------------');
    console.log(`✅ User "${username}" (${email}) updated successfully!`);
    console.log(`   Username: ${username}`);
    console.log(`   Email:    ${email}`);
    console.log(`   Role:     ${isAdmin ? 'Root Administrator' : 'Standard User'}`);
    console.log('----------------------------------------------');
    process.exit(0);
  }

  await db.query(
    `INSERT INTO users (first_name, last_name, username, email, password, root_admin, status) 
     VALUES (?, ?, ?, ?, ?, ?, 'active')`,
    [firstName, lastName, username, email, hashedPassword, isAdmin]
  );

  console.log('----------------------------------------------');
  console.log('✅ User successfully created in Nova Panel!');
  console.log(`   Username: ${username}`);
  console.log(`   Email:    ${email}`);
  console.log(`   Role:     ${isAdmin ? 'Root Administrator' : 'Standard User'}`);
  console.log('----------------------------------------------');
  process.exit(0);
}

run().catch((err) => {
  console.error('❌ Failed to create user:', err);
  process.exit(1);
});
