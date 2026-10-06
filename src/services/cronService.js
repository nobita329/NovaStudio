const cron = require('node-cron');
const { getDb } = require('../database');
const dockerService = require('../docker/dockerService');

const scheduledJobs = new Map();

async function initCronService() {
  const db = getDb();
  if (!db) return;

  try {
    const schedules = await db.query('SELECT * FROM schedules WHERE is_active = 1');
    for (const schedule of schedules) {
      registerJob(schedule);
    }
    console.log(`[Nova Cron] Loaded ${schedules.length} active schedules.`);
  } catch (err) {
    console.error('[Nova Cron] Failed to initialize schedules:', err.message);
  }
}

function registerJob(schedule) {
  if (scheduledJobs.has(schedule.id)) {
    scheduledJobs.get(schedule.id).stop();
    scheduledJobs.delete(schedule.id);
  }

  if (!cron.validate(schedule.cron_expression)) {
    console.warn(`[Nova Cron] Invalid cron expression "${schedule.cron_expression}" for schedule ${schedule.id}`);
    return;
  }

  const job = cron.schedule(schedule.cron_expression, async () => {
    console.log(`[Nova Cron] Triggering schedule #${schedule.id} (${schedule.name}) for server ${schedule.server_id}`);
    const db = getDb();
    try {
      await db.query('UPDATE schedules SET last_run = CURRENT_TIMESTAMP WHERE id = ?', [schedule.id]);

      const tasks = await db.query(
        'SELECT * FROM schedule_tasks WHERE schedule_id = ? ORDER BY sequence_order ASC',
        [schedule.id]
      );

      for (const task of tasks) {
        if (task.time_offset > 0) {
          await new Promise((r) => setTimeout(r, task.time_offset * 1000));
        }

        if (task.action_type === 'command') {
          await dockerService.sendCommand(schedule.server_id, task.payload);
        } else if (task.action_type === 'power') {
          if (task.payload === 'start') await dockerService.startServer(schedule.server_id, db);
          if (task.payload === 'stop') await dockerService.stopServer(schedule.server_id, db);
          if (task.payload === 'restart') await dockerService.restartServer(schedule.server_id, db);
        }
      }
    } catch (err) {
      console.error(`[Nova Cron] Error executing schedule ${schedule.id}:`, err);
    }
  });

  scheduledJobs.set(schedule.id, job);
}

function unregisterJob(scheduleId) {
  if (scheduledJobs.has(scheduleId)) {
    scheduledJobs.get(scheduleId).stop();
    scheduledJobs.delete(scheduleId);
  }
}

module.exports = {
  initCronService,
  registerJob,
  unregisterJob,
};
