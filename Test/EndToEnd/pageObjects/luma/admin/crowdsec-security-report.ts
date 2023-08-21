import { expect, Page } from "@playwright/test";

export default class CrowdSecSecurityReportPage {
  page: Page;
  url: string;

  constructor(page: Page) {
    this.url = "/admin/crowdsec-engine/events/grid/";
    this.page = page;
  }

  public async navigateTo() {
    await this.page.goto(this.url);
    await expect(this.page).toHaveTitle(/CrowdSec Engine/);
  }
}
