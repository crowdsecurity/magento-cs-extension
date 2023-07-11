import { test as baseTest } from "@playwright/test";

import AdminLoginPage from "../pageObjects/luma/admin/login";
import AdminCrowdSecSecurityConfigPage from "../pageObjects/luma/admin/crowdsec-security-config";
import AdminCrowdSecSecurityReportPage from "../pageObjects/luma/admin/crowdsec-security-report";
import HomePage from "../pageObjects/luma/home";
import NoRoutePage from "../pageObjects/luma/no-route";
import RunActionPage from "../pageObjects/runAction";
import RunCronPage from "../pageObjects/runCron";
import screenshotOnFailure from "./helpers/screenshot";

type pages = {
  adminCrowdSecSecurityConfigPage: AdminCrowdSecSecurityConfigPage;
  adminCrowdSecSecurityReportPage: AdminCrowdSecSecurityReportPage;
  adminLoginPage: AdminLoginPage;
  homePage: HomePage;
  noRoutePage: NoRoutePage;
  runActionPage: RunActionPage;
  runCronPage: RunCronPage;
  screenshotOnFailure: void;
};

const testPages = baseTest.extend<pages>({
  adminCrowdSecSecurityConfigPage: async ({ page }, use) => {
    await use(new AdminCrowdSecSecurityConfigPage(page));
  },
  adminCrowdSecSecurityReportPage: async ({ page }, use) => {
    await use(new AdminCrowdSecSecurityReportPage(page));
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
  runActionPage: async ({ page }, use) => {
    await use(new RunActionPage(page));
  },
  runCronPage: async ({ page }, use) => {
    await use(new RunCronPage(page));
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
