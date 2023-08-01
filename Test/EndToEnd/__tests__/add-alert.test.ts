// @ts-check
import { test, expect } from "../fixtures";

import { deleteFileContent, getFileContent } from "../helpers/log";
import { wait } from "../helpers/time";
import { LOG_PATH, blockRegex } from "../helpers/constants";

test.describe("Add alert test", () => {
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

  test("can add an alert", async ({
    runActionPage,
    homePage,
    page,
    adminCrowdSecSecurityReportPage,
    adminCrowdSecSecurityConfigPage,
  }) => {
    const scenario = "test-playwright";
    await runActionPage.clearCache();
    const ip = await runActionPage.getIp();
    // Delete all previous events fo IP
    await runActionPage.deleteEvents(ip);
    // Chek report page
    await adminCrowdSecSecurityReportPage.navigateTo();
    await expect(page.locator("body")).not.toHaveText(
      new RegExp(`addAlertTest/${scenario}`)
    );

    await runActionPage.addAlert(ip, scenario);

    let logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(
      new RegExp(
        `Triggered alert will be saved {"ip":"${ip}","scenario":"addAlertTest/${scenario}"}`
      )
    );

    await homePage.navigateTo(false);
    await expect(page.locator("body")).toHaveText(blockRegex);
    logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(
      new RegExp(
        `Alert triggered {"ip":"${ip}","scenario":"addAlertTest/${scenario}"}`
      )
    );
    // Test that multiple add is not possible
    await runActionPage.addAlert(ip, scenario);
    logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(new RegExp(`Alert already in queue`));

    // Clear Cache to be able to access admin
    await runActionPage.clearCache();
    // Chek report page
    await adminCrowdSecSecurityReportPage.navigateTo();
    await expect(page.locator("body")).toHaveText(
      new RegExp(`addAlertTest/${scenario}`)
    );
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
    await deleteFileContent(LOG_PATH);
    await runActionPage.addAlert(ip, scenario);
    logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(new RegExp(`Event is in black hole`));
    expect(logContent).not.toMatch(
      new RegExp(
        `Alert triggered {"ip":"${ip}","scenario":"addAlertTest/${scenario}"}`
      )
    );
  });

  test("should log error", async ({ runActionPage, page }) => {
    const badNamedScenario = "test-playwright()";
    await runActionPage.clearCache();
    const ip = "5.6.7.8";

    await runActionPage.addAlert(ip, badNamedScenario);
    await expect(page.locator("body")).toHaveText("false");

    const logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(
      new RegExp(`Scenario name does not conform to the convention`)
    );
  });
});

test.describe("Add alert test with event", () => {
  test.beforeEach(async () => {
    // Clean log file
    await deleteFileContent(LOG_PATH);
    const logContent = await getFileContent(LOG_PATH);
    expect(logContent).toBe("");
  });

  test("can set default config with event", async ({
    adminCrowdSecSecurityConfigPage,
  }) => {
    await adminCrowdSecSecurityConfigPage.navigateTo();
    await adminCrowdSecSecurityConfigPage.setDefaultConfig();
  });

  test("can add an alert with event", async ({
    runActionPage,
    homePage,
    page,
    adminCrowdSecSecurityReportPage,
    adminCrowdSecSecurityConfigPage,
  }) => {
    const scenario = "test-playwright";
    await runActionPage.clearCache();
    const ip = await runActionPage.getIp();
    // Delete all previous events fo IP
    await runActionPage.deleteEvents(ip);
    // Chek report page
    await adminCrowdSecSecurityReportPage.navigateTo();
    await expect(page.locator("body")).not.toHaveText(
      new RegExp(`testAlertEvent/${scenario}`)
    );

    await runActionPage.addAlertByEvent(ip, scenario);

    let logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(
      new RegExp(
        `Triggered alert will be saved {"ip":"${ip}","scenario":"testAlertEvent/${scenario}"}`
      )
    );

    await homePage.navigateTo(false);
    await expect(page.locator("body")).toHaveText(blockRegex);
    logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(
      new RegExp(
        `Alert triggered {"ip":"${ip}","scenario":"testAlertEvent/${scenario}"}`
      )
    );
    // Test that multiple add is not possible
    await runActionPage.addAlertByEvent(ip, scenario);
    logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(new RegExp(`Alert already in queue`));

    // Clear Cache to be able to access admin
    await runActionPage.clearCache();
    // Chek report page
    await adminCrowdSecSecurityReportPage.navigateTo();
    await expect(page.locator("body")).toHaveText(
      new RegExp(`testAlertEvent/${scenario}`)
    );
    // Push signals manually
    await wait(10000); // Wait 10 seconds before pushing again
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
    await deleteFileContent(LOG_PATH);
    await runActionPage.addAlertByEvent(ip, scenario);
    logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(new RegExp(`Event is in black hole`));
    expect(logContent).not.toMatch(
      new RegExp(
        `Alert triggered {"ip":"${ip}","scenario":"testAlertEvent/${scenario}"}`
      )
    );
  });

  test("should log error for event", async ({ runActionPage, page }) => {
    const badNamedScenario = "test-playwright()";
    await runActionPage.clearCache();
    const ip = "5.6.7.8";

    await runActionPage.addAlertByEvent(ip, badNamedScenario);
    await expect(page.locator("body")).toHaveText("dispatched");

    const logContent = await getFileContent(LOG_PATH);
    expect(logContent).toMatch(
      new RegExp(`Scenario name does not conform to the convention`)
    );
  });
});
