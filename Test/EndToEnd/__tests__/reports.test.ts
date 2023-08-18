// @ts-check
import { test } from "../fixtures";

test.describe("Reports page", () => {
  test("can navigate", async ({ adminCrowdSecSecurityReportPage }) => {
    await adminCrowdSecSecurityReportPage.navigateTo();
  });
});
