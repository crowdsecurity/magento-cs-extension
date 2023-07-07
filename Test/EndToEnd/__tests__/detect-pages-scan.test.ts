// @ts-check
import { test, expect } from "../fixtures";

test.describe("Detect pages scan", () => {
  test("should be banned if too many try", async ({
    runActionsPage,
    noRoutePage,
    page,
  }) => {
    await runActionsPage.clearCache();
    const ip = await runActionsPage.getIp();
    // Delete all precious events fo IP
    await runActionsPage.deleteEvents(ip);

    for (let i = 0; i < 10; i++) {
      await noRoutePage.navigateTo();
    }

    const blockRegex = /has been blocked/;
    expect(page.locator("body")).not.toHaveText(blockRegex);

    await noRoutePage.navigateTo(false);

    await expect(page.locator("body")).toHaveText(blockRegex);

    await runActionsPage.clearCache();

    //@TODO : try to push signals and count result > 1
  });
});
