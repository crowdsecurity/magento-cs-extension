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

  test("should succed to enroll with empty key", async ({
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
