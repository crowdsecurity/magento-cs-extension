import { test as baseTest } from "@playwright/test";
import HomePage from "../pageObjects/home";
import screenshotOnFailure from "./helpers/screenshot";

type pages = {
  homePage: HomePage;
  screenshotOnFailure: void;
};

const testPages = baseTest.extend<pages>({
  homePage: async ({ page }, use) => {
    await use(new HomePage(page));
  },
  screenshotOnFailure: [
    async ({ page }, use, testInfo) => {
      await use();
      await screenshotOnFailure({ page }, testInfo);
    },
    { auto: true },
  ],
});

export const test = testPages;
export const expect = testPages.expect;
export const describe = testPages.describe;
