// @ts-check
import { test, expect } from "../fixtures";
import { testIp1, ORIGIN_CROWDSEC, blockRegex } from "../helpers/constants";

test.describe("Extension configuration", () => {
  test.beforeEach(async ({ adminCrowdSecSecurityConfigPage }) => {
    await adminCrowdSecSecurityConfigPage.navigateTo();
  });

  test("can set default config", async ({
    adminCrowdSecSecurityConfigPage,
  }) => {
    await adminCrowdSecSecurityConfigPage.setDefaultConfig();
  });

  test("should succeed to enroll", async ({
    adminCrowdSecSecurityConfigPage,
  }) => {
    await adminCrowdSecSecurityConfigPage.enroll();
  });

  test("should clear cache", async ({ adminCrowdSecSecurityConfigPage }) => {
    await adminCrowdSecSecurityConfigPage.clearCache();
  });

  test("should refresh cache", async ({ adminCrowdSecSecurityConfigPage }) => {
    await adminCrowdSecSecurityConfigPage.refreshCache();
  });
});

test.describe("Fallback remediation setting", () => {
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
    // Local decision should be 1
    await expect(
      page.locator("#crowdsec-engine-metrics tbody tr:first-child td.count")
    ).toHaveText("1");

    await adminCrowdSecSecurityConfigPage.navigateTo();
    await page
      .getByRole("combobox", { name: "[GLOBAL] Fallback" })
      .selectOption("ban");

    await adminCrowdSecSecurityConfigPage.saveConfig(false);
    // Home page should be blocked as fallback is now ban
    await homePage.navigateTo(false);
    await expect(page.locator("body")).toHaveText(blockRegex);

    // Clear cache for next tests and reinit forced test ip
    await runActionPage.setForcedIp("");
    await runActionPage.clearCache();
  });
});
