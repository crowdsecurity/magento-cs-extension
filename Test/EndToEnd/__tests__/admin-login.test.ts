// @ts-check
import { test } from "../fixtures";

test("can login", async ({ adminLoginPage }) => {
  await adminLoginPage.navigateTo();
  await adminLoginPage.login("admin", "admin123");
});
