import { expect, Page } from "@playwright/test";

export default class RunActionPage {
  page: Page;
  url: string;

  constructor(page: Page) {
    this.url = "/runActions.php?action=";
    this.page = page;
  }
  /**
   *
   * @param action
   * @param params
   */
  public async navigateTo(action: string, params = "") {
    const url = this.url + action + params;
    await this.page.goto(url);
    await expect(this.page).toHaveTitle(`Action: ${action}`);
  }

  public async getIp() {
    await this.navigateTo("get-ip");
    const result = await this.page.locator("h1").innerText();
    expect(result).toMatch(/^((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4}$/);

    return result;
  }

  public async clearCache() {
    await this.navigateTo("clear");

    const result = await this.page.locator("h1").innerText();
    expect(result).toBe("true");
  }

  public async deleteEvents(ip: string) {
    await this.navigateTo("delete-events", `&ip=${ip}`);

    const result = await this.page.locator("h1").innerText();
    expect(parseInt(result)).toBeGreaterThanOrEqual(0);
  }
}
