// @ts-check
import { test, expect } from "../fixtures";
import {
  testIp1,
  ORIGIN_CROWDSEC,
  ORIGIN_LISTS,
  ORIGIN_CAPI,
  blockRegex,
} from "../helpers/constants";

test.describe("Reports page", () => {
  test("can set default config", async ({
    adminCrowdSecSecurityConfigPage,
  }) => {
    await adminCrowdSecSecurityConfigPage.navigateTo();
    await adminCrowdSecSecurityConfigPage.setDefaultConfig();
  });
  test("can see default settings", async ({
    adminCrowdSecSecurityReportPage,
    page,
    runActionPage,
  }) => {
    await runActionPage.clearCache();
    await adminCrowdSecSecurityReportPage.navigateTo();
    await expect(
      page.locator("#crowdsec-engine-remediation-metrics")
    ).toHaveText(/Ban IP locally setting is enabled/);
    await expect(
      page.locator("#crowdsec-engine-remediation-metrics")
    ).toHaveText(/Block banned IP setting is enabled/);
    await expect(
      page.locator("#crowdsec-engine-remediation-metrics")
    ).toHaveText(/Fallback setting is bypass/);
    // Local decision should be 0
    await expect(
      page.locator("#crowdsec-engine-metrics tbody tr:first-child td.count")
    ).toHaveText("0");
    // Community Blocklist decision should be 0
    await expect(
      page.locator("#crowdsec-engine-metrics tbody tr:nth-child(2) td.count")
    ).toHaveText("0");
    // Third party blocklist decision should be 0
    await expect(
      page.locator("#crowdsec-engine-metrics tbody tr:last-child td.count")
    ).toHaveText("0");
  });

  test("can see modified settings", async ({
    adminCrowdSecSecurityConfigPage,
    adminCrowdSecSecurityReportPage,
    page,
  }) => {
    await adminCrowdSecSecurityConfigPage.navigateTo();
    await page
      .getByRole("combobox", {
        name: "[GLOBAL] Ban IP locally",
      })
      .selectOption("0");

    await page
      .getByRole("combobox", { name: "[GLOBAL] Block banned IP" })
      .selectOption("0");

    await page
      .getByRole("combobox", { name: "[GLOBAL] Fallback" })
      .selectOption("ban");

    await adminCrowdSecSecurityConfigPage.saveConfig();
    await adminCrowdSecSecurityReportPage.navigateTo();
    await expect(
      page.locator("#crowdsec-engine-remediation-metrics")
    ).toHaveText(/Ban IP locally setting is disabled/);
    await expect(
      page.locator("#crowdsec-engine-remediation-metrics")
    ).toHaveText(/Block banned IP setting is disabled/);
    await expect(
      page.locator("#crowdsec-engine-remediation-metrics")
    ).toHaveText(/Fallback setting is ban/);
  });

  test("can see count incrementation for local decision", async ({
    adminCrowdSecSecurityConfigPage,
    adminCrowdSecSecurityReportPage,
    runActionPage,
    page,
    homePage,
  }) => {
    await adminCrowdSecSecurityConfigPage.navigateTo();
    // We need the Block banned IP feature to be enabled for getting remediation
    await page
      .getByRole("combobox", { name: "[GLOBAL] Block banned IP" })
      .selectOption("1");

    await page
      .getByRole("combobox", { name: "[GLOBAL] Fallback" })
      .selectOption("bypass");
    await adminCrowdSecSecurityConfigPage.saveConfig();
    await adminCrowdSecSecurityReportPage.navigateTo();
    // Local decision should be 0
    await expect(
      page.locator("#crowdsec-engine-metrics tbody tr:first-child td.count")
    ).toHaveText("0");

    // Simulate a ban decision for testIp1
    await runActionPage.addDecision(testIp1, "ban", ORIGIN_CROWDSEC, 60);
    await runActionPage.setForcedIp(testIp1);
    await homePage.navigateTo(false);
    // Reset forced ip to be able to access pages
    await runActionPage.setForcedIp("");
    await adminCrowdSecSecurityReportPage.navigateTo();
    // Local decision should be 1
    await expect(
      page.locator("#crowdsec-engine-metrics tbody tr:first-child td.count")
    ).toHaveText("1");
    // Clear cache for next tests
    await runActionPage.clearCache();
  });

  test("can see count incrementation for capi", async ({
    adminCrowdSecSecurityConfigPage,
    adminCrowdSecSecurityReportPage,
    runActionPage,
    page,
    homePage,
  }) => {
    await adminCrowdSecSecurityConfigPage.navigateTo();
    // We need the Block banned IP feature to be enabled for getting remediation
    await page
      .getByRole("combobox", { name: "[GLOBAL] Block banned IP" })
      .selectOption("1");

    await page
      .getByRole("combobox", { name: "[GLOBAL] Fallback" })
      .selectOption("bypass");
    await adminCrowdSecSecurityConfigPage.saveConfig();
    await adminCrowdSecSecurityReportPage.navigateTo();
    // Community Blocklist decision should be 0
    await expect(
      page.locator("#crowdsec-engine-metrics tbody tr:nth-child(2) td.count")
    ).toHaveText("0");

    // Simulate a ban decision for testIp1
    await runActionPage.addDecision(testIp1, "ban", ORIGIN_CAPI, 60);
    await runActionPage.setForcedIp(testIp1);
    await homePage.navigateTo(false);
    // Reset forced ip to be able to access pages
    await runActionPage.setForcedIp("");
    await adminCrowdSecSecurityReportPage.navigateTo();
    // Community Blocklist decision should be 1
    await expect(
      page.locator("#crowdsec-engine-metrics tbody tr:nth-child(2) td.count")
    ).toHaveText("1");
    // Clear cache for next tests
    await runActionPage.clearCache();
  });

  test("can see count incrementation for thir-party blocklists", async ({
    adminCrowdSecSecurityConfigPage,
    adminCrowdSecSecurityReportPage,
    runActionPage,
    page,
    homePage,
  }) => {
    await adminCrowdSecSecurityConfigPage.navigateTo();
    // We need the Block banned IP feature to be enabled for getting remediation
    await page
      .getByRole("combobox", { name: "[GLOBAL] Block banned IP" })
      .selectOption("1");

    await page
      .getByRole("combobox", { name: "[GLOBAL] Fallback" })
      .selectOption("bypass");
    await adminCrowdSecSecurityConfigPage.saveConfig();
    await adminCrowdSecSecurityReportPage.navigateTo();
    // Third party blocklist decision should be 0
    await expect(
      page.locator("#crowdsec-engine-metrics tbody tr:last-child td.count")
    ).toHaveText("0");

    // Simulate a ban decision for testIp1
    await runActionPage.addDecision(testIp1, "ban", ORIGIN_LISTS, 60);
    await runActionPage.setForcedIp(testIp1);
    await homePage.navigateTo(false);
    // Reset forced ip to be able to access pages
    await runActionPage.setForcedIp("");
    await adminCrowdSecSecurityReportPage.navigateTo();
    /// Third party blocklist decision should be 1
    await expect(
      page.locator("#crowdsec-engine-metrics tbody tr:last-child td.count")
    ).toHaveText("1");
    // Clear cache for next tests
    await runActionPage.clearCache();
  });
});
