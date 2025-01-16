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
  test("can see default settings", async ({
    adminCrowdSecSecurityReportPage,
    adminCrowdSecSecurityConfigPage,
    page,
    runActionPage,
  }) => {
    await runActionPage.clearCache();
    await adminCrowdSecSecurityConfigPage.navigateTo();
    await adminCrowdSecSecurityConfigPage.setDefaultConfig();
    await adminCrowdSecSecurityReportPage.navigateTo();
    await expect(
      page.locator("#crowdsec-engine-remediation-metrics")
    ).toHaveText(/Ban IP locally enabled/);
    await expect(
      page.locator("#crowdsec-engine-remediation-metrics")
    ).toHaveText(/Block banned IP enabled/);

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

    await adminCrowdSecSecurityConfigPage.saveConfig();
    await adminCrowdSecSecurityReportPage.navigateTo();
    await expect(
      page.locator("#crowdsec-engine-remediation-metrics")
    ).toHaveText(/Ban IP locally disabled/);
    await expect(
      page.locator("#crowdsec-engine-remediation-metrics")
    ).toHaveText(/Block banned IP disabled/);
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

    await adminCrowdSecSecurityConfigPage.saveConfig();
    await adminCrowdSecSecurityReportPage.navigateTo();
    // Third party blocklist decision should be 0
    await expect(
      page.locator("#crowdsec-engine-metrics tbody tr:last-child td.count")
    ).toHaveText("0");

    // Simulate a ban decision for testIp1
    await runActionPage.addDecision(testIp1, "ban", `${ORIGIN_LISTS}:tor`, 60);
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

  test("can see count incrementation for local decision with unhandled remediation", async ({
    adminCrowdSecSecurityConfigPage,
    adminCrowdSecSecurityReportPage,
    runActionPage,
    page,
    homePage,
  }) => {
    // Reset all
    await runActionPage.setForcedIp("");
    await runActionPage.clearCache();
    // Set config
    await adminCrowdSecSecurityConfigPage.navigateTo();
    await adminCrowdSecSecurityConfigPage.setDefaultConfig();
    await adminCrowdSecSecurityReportPage.navigateTo();
    // Local decision should be 0
    await expect(
      page.locator("#crowdsec-engine-metrics tbody tr:first-child td.count")
    ).toHaveText("0");

    // Simulate a "unhandled" decision for testIp1
    await runActionPage.addDecision(testIp1, "unhandled", ORIGIN_CROWDSEC, 60);
    await runActionPage.setForcedIp(testIp1);
    // Home page should be accessible as default fallback is bypass
    await homePage.navigateTo();
    await expect(page.locator("body")).not.toHaveText(blockRegex);
    await adminCrowdSecSecurityReportPage.navigateTo();
    // Local decision should be 0 as bypass is not counted in crowdsec origin
    await expect(
      page.locator("#crowdsec-engine-metrics tbody tr:first-child td.count")
    ).toHaveText("0");

    // Clear cache for next tests and reinit forced test ip
    await runActionPage.setForcedIp("");
    await runActionPage.clearCache();
  });
});
