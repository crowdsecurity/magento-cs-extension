import { test as baseTest } from "@playwright/test";

import AdminLoginPage from "../pageObjects/luma/admin/login";
import AdminCrowdSecSecurityConfigPage from "../pageObjects/luma/admin/crowdsec-security-config";
import HomePage from "../pageObjects/luma/home";
import NoRoutePage from "../pageObjects/luma/no-route";
import RunActionsPage from "../pageObjects/runActions";
import screenshotOnFailure from "./helpers/screenshot";

type pages = {
  adminCrowdSecSecurityConfigPage: AdminCrowdSecSecurityConfigPage;
  adminLoginPage: AdminLoginPage;
  homePage: HomePage;
  noRoutePage: NoRoutePage;
  runActionsPage: RunActionsPage;
  screenshotOnFailure: void;
};

const testPages = baseTest.extend<pages>({
  adminCrowdSecSecurityConfigPage: async ({ page }, use) => {
    await use(new AdminCrowdSecSecurityConfigPage(page));
  },
  adminLoginPage: async ({ page }, use) => {
    await use(new AdminLoginPage(page));
  },
  homePage: async ({ page }, use) => {
    await use(new HomePage(page));
  },
  noRoutePage: async ({ page }, use) => {
    await use(new NoRoutePage(page));
  },
  runActionsPage: async ({ page }, use) => {
    await use(new RunActionsPage(page));
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
