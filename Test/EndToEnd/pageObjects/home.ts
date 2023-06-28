import { expect, Page } from "@playwright/test";

export default class HomePage {
  url: string;

  constructor(public page: Page) {
    this.url = "/";
  }

  public async navigateTo() {
    await this.page.goto(this.url);
    await expect(this.page).toHaveTitle(/Home page/);
  }
}
