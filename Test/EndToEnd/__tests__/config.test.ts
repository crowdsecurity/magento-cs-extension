// @ts-check
import { test } from "../fixtures";

test.describe("Extension configuration", () => {
  test.beforeEach(async ({ adminCrowdSecSecurityConfigPage }) => {
    await adminCrowdSecSecurityConfigPage.navigateTo();
  });

  test("can set default config", async ({
    adminCrowdSecSecurityConfigPage,
  }) => {
    await adminCrowdSecSecurityConfigPage.setDefaultConfig();
  });

  test("should failed to enroll with empty key", async ({
    adminCrowdSecSecurityConfigPage,
    page,
  }) => {
    await page.getByLabel("Enrollment key").fill("");
    await page
      .locator("#row_crowdsec_engine_general_enroll")
      .getByRole("cell")
      .first()
      .click();
    await adminCrowdSecSecurityConfigPage.enroll(false);
  });

  test("should clear cache", async ({ adminCrowdSecSecurityConfigPage }) => {
    await adminCrowdSecSecurityConfigPage.clearCache();
  });

  test("should refresh cache", async ({ adminCrowdSecSecurityConfigPage }) => {
    await adminCrowdSecSecurityConfigPage.refreshCache();
  });
});
