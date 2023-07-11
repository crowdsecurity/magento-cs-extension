import { expect, Page } from "@playwright/test";

export default class HomePage {
  page: Page;
  url: string;

  constructor(page: Page) {
    this.url = "/";
    this.page = page;
  }

  public async navigateTo(check = true) {
    await this.page.goto(this.url);
    if (check) {
      await expect(this.page).toHaveTitle(/Home page/);
    }
  }
}
