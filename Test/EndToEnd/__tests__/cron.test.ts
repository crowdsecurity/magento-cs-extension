// @ts-check
import { test, expect } from "../fixtures";

import { deleteFileContent, getFileContent } from "../helpers/log";
import { LOG_PATH } from "../helpers/constants";

test.describe("Cron test", () => {
  test.beforeEach(async () => {
    // Clean log file
    await deleteFileContent(LOG_PATH);
    const logContent = await getFileContent(LOG_PATH);
    expect(logContent).toBe("");
  });

  test("can set default config", async ({
    adminCrowdSecSecurityConfigPage,
  }) => {
    await adminCrowdSecSecurityConfigPage.navigateTo();
    await adminCrowdSecSecurityConfigPage.setDefaultConfig();
  });

  test("can prune cache by cron", async ({ runCronPage }) => {
    await runCronPage.pruneCache();
    const logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(new RegExp(`Cache has been pruned by cron`));
  });

  test("can refresh cache by cron", async ({ runCronPage }) => {
    await runCronPage.refreshCache();
    const logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(new RegExp(`Cache has been refreshed by cron`));
  });

  test("can clean old events by cron", async ({ runCronPage }) => {
    await runCronPage.cleanEvents();
    const logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(new RegExp(`Old events have been deleted`));
  });

  test("can push signals by cron", async ({ runCronPage }) => {
    await runCronPage.pushSignals();
    const logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(new RegExp(`Signals have been pushed`));
  });
});
