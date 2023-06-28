// @ts-check
import { test } from "../fixtures";

test("has title", async ({ homePage }) => {
  await homePage.navigateTo();
});
