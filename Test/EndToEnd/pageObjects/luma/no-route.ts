import { expect, Page } from "@playwright/test";

export default class NoRoutePage {
  page: Page;
  url: string;

  constructor(page: Page) {
    this.url = "/this-url-gives-a-404";
    this.page = page;
  }

  public async navigateTo(check = true) {
    await this.page.goto(this.url);
    if (check) {
      await expect(this.page).toHaveTitle(/404 Not Found/);
    }
  }
}
