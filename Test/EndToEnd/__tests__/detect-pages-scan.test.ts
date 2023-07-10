// @ts-check
import { test, expect } from "../fixtures";
import { deleteFileContent, getFileContent } from "../helpers/log";
import { LOG_PATH } from "../helpers/constants";

test.describe("Detect pages scan", () => {
  test.beforeEach(async () => {
    // Clean log file
    await deleteFileContent(LOG_PATH);
    const logContent = await getFileContent(LOG_PATH);
    expect(logContent).toBe("");
  });

  test("should be banned if too many try", async ({
    runActionsPage,
    noRoutePage,
    adminCrowdSecSecurityConfigPage,
    page,
  }) => {
    await runActionsPage.clearCache();
    const ip = await runActionsPage.getIp();
    // Delete all precious events fo IP
    await runActionsPage.deleteEvents(ip);

    for (let i = 0; i < 10; i++) {
      await noRoutePage.navigateTo();
    }
    let logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(
      new RegExp(
        `Detected event {"ip":"${ip}","scenario":"magento2/pages-scan"}`
      )
    );
    // With 10 detection, alert should not have been triggered
    const blockRegex = /has been blocked/;
    expect(page.locator("body")).not.toHaveText(blockRegex);

    await noRoutePage.navigateTo(false);
    // With 11 detection, alert should not have been triigered
    await expect(page.locator("body")).toHaveText(blockRegex);
    // Clear chache to be able to access admin pages
    await runActionsPage.clearCache();

    // Push signals manually
    await adminCrowdSecSecurityConfigPage.navigateTo();
    await adminCrowdSecSecurityConfigPage.pushSignals();
    await expect(page.locator("#signals_push_result")).toContainText(
      /0 errors for 1 candidates/,
      {
        timeout: 30000,
      }
    );
    logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(
      new RegExp(
        `Signals have been pushed {"candidates":1,"pushed":1,"errors":0}`
      )
    );

    await noRoutePage.navigateTo();
    logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(new RegExp(`Event is in black hole`));
  });
});
