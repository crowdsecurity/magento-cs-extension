// @ts-check
import { test, expect } from "../fixtures";
import { deleteFileContent, getFileContent } from "../helpers/log";
import { LOG_PATH, adminName, adminPwd } from "../helpers/constants";

const badUsers = [
  "aaa1",
  "aaa2",
  "aaa3",
  "aaa4",
  "aaa5",
  "aaa6",
  "aaa7",
  "aaa8",
  "aaa9",
  "aaa10",
];

test.describe("Detect user enum", () => {
  test.beforeEach(async () => {
    // Clean log file
    await deleteFileContent(LOG_PATH);
    const logContent = await getFileContent(LOG_PATH);
    expect(logContent).toBe("");
  });

  test("should be banned if too many enumeration", async ({
    runActionPage,
    adminLoginPage,
    adminCrowdSecSecurityConfigPage,
    homePage,
    page,
  }) => {
    await runActionPage.clearCache();
    const ip = await runActionPage.getIp();
    // Delete all precious events fo IP
    await runActionPage.deleteEvents(ip);

    await adminLoginPage.logout();

    for (const user of badUsers) {
      await adminLoginPage.login(user, "password", false);
    }

    let logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(
      new RegExp(
        `Detected event {"ip":"${ip}","scenario":"magento2/user-enum"}`
      )
    );
    // With 10 detection, alert should not have been triggered
    await homePage.navigateTo();
    const blockRegex = /has been blocked/;
    expect(page.locator("body")).not.toHaveText(blockRegex);

    await adminLoginPage.navigateTo();
    await adminLoginPage.login("another_bad_name", "password", false);
    // With 11 detection, alert should not have been triigered
    await expect(page.locator("body")).toHaveText(blockRegex);
    // Clear chache to be able to access admin pages
    await runActionPage.clearCache();

    // Push signals manually
    await adminLoginPage.navigateTo();
    await adminLoginPage.login(adminName, adminPwd);
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
    // Test that event has been detected as in "black hole"
    await adminLoginPage.logout();
    await adminLoginPage.login("another_bad_name_bis", "password", false);

    logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(new RegExp(`Event is in black hole`));
  });
});
