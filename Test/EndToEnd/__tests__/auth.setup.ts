import { test as setup } from "../fixtures";

const authFile = "__tests__/.auth/user.json";

setup("authenticate", async ({ adminLoginPage, page }) => {
  await adminLoginPage.navigateTo();
  await adminLoginPage.login("admin", "admin123");
  // End of authentication steps.
  await page.context().storageState({ path: authFile });
});
