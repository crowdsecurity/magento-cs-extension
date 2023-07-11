// @ts-check
import { test, expect } from "../fixtures";
import { deleteFileContent, getFileContent } from "../helpers/log";
import { LOG_PATH, blockRegex } from "../helpers/constants";

test.describe("Detect pages scan", () => {
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

  test("should be banned if too many try", async ({
    runActionPage,
    noRoutePage,
    adminCrowdSecSecurityConfigPage,
    page,
  }) => {
    await runActionPage.clearCache();
    const ip = await runActionPage.getIp();
    // Delete all previous events fo IP
    await runActionPage.deleteEvents(ip);

    for (let i = 0; i < 10; i++) {
      await noRoutePage.navigateTo();
    }
    let logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(
      new RegExp(
        `Detected event saved {"ip":"${ip}","scenario":"magento2/pages-scan"}`
      )
    );
    // With 10 detection, alert should not have been triggered
    expect(page.locator("body")).not.toHaveText(blockRegex);

    await noRoutePage.navigateTo(false);
    // With 11 detection, alert should have been triggered
    await expect(page.locator("body")).toHaveText(blockRegex);
    // Clear cache to be able to access admin pages
    await runActionPage.clearCache();

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
    // Test that event has been detected as in "black hole"
    await noRoutePage.navigateTo();
    logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(new RegExp(`Event is in black hole`));
  });

  test("should NOT be banned if too many try (because disabled bounce)", async ({
    runActionPage,
    noRoutePage,
    adminCrowdSecSecurityConfigPage,
    page,
  }) => {
    await runActionPage.clearCache();
    const ip = await runActionPage.getIp();
    // Delete all previous events fo IP
    await runActionPage.deleteEvents(ip);

    //Change config
    await adminCrowdSecSecurityConfigPage.navigateTo();
    await page
      .getByRole("combobox", { name: "[GLOBAL] Bounce banned IP" })
      .selectOption("0");
    await adminCrowdSecSecurityConfigPage.saveConfig();

    for (let i = 0; i < 10; i++) {
      await noRoutePage.navigateTo();
    }
    let logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(
      new RegExp(
        `Detected event saved {"ip":"${ip}","scenario":"magento2/pages-scan"}`
      )
    );
    // With 10 detection, alert should not have been triggered
    expect(page.locator("body")).not.toHaveText(blockRegex);

    await noRoutePage.navigateTo();
    // With 11 detection, alert should have been triggered but not blocked because of settings
    await expect(page.locator("body")).not.toHaveText(blockRegex);

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
    // Test that event has been detected as in "black hole"
    await noRoutePage.navigateTo();
    logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(new RegExp(`Event is in black hole`));
  });

  test("should NOT be banned if too many try (because disabled local ban)", async ({
    runActionPage,
    noRoutePage,
    adminCrowdSecSecurityConfigPage,
    page,
  }) => {
    await runActionPage.clearCache();
    const ip = await runActionPage.getIp();
    // Delete all previous events fo IP
    await runActionPage.deleteEvents(ip);

    //Change config
    await adminCrowdSecSecurityConfigPage.navigateTo();
    await page
      .getByRole("combobox", { name: "[GLOBAL] Bounce banned IP" })
      .selectOption("1");
    await page
      .getByRole("combobox", {
        name: "[GLOBAL] Ban IP locally when a scenario triggers an alert",
      })
      .selectOption("0");
    await adminCrowdSecSecurityConfigPage.saveConfig();

    for (let i = 0; i < 10; i++) {
      await noRoutePage.navigateTo();
    }
    let logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(
      new RegExp(
        `Detected event saved {"ip":"${ip}","scenario":"magento2/pages-scan"}`
      )
    );
    // With 10 detection, alert should not have been triggered
    expect(page.locator("body")).not.toHaveText(blockRegex);

    await noRoutePage.navigateTo();
    // With 11 detection, alert should have been triggered but not blocked because of settings
    await expect(page.locator("body")).not.toHaveText(blockRegex);

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
    // Test that event has been detected as in "black hole"
    await noRoutePage.navigateTo();
    logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(new RegExp(`Event is in black hole`));
  });
});
