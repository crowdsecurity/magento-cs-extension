import { expect, Page } from "@playwright/test";

export default class HomePage {
  page: Page;
  url: string;

  constructor(page: Page) {
    this.url = "/";
    this.page = page;
  }

  public async navigateTo() {
    await this.page.goto(this.url);
    await expect(this.page).toHaveTitle(/Home page/);
  }
}
