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

  public async addAlert(ip: string, scenario: string) {
    await this.navigateTo("add-alert", `&ip=${ip}&scenario=${scenario}`);
    const result = await this.page.locator("h1").innerText();
    expect(result).toMatch(/true|false/);
  }

  public async addAlertByEvent(ip: string, scenario: string) {
    await this.navigateTo(
      "add-alert-by-event",
      `&ip=${ip}&scenario=${scenario}`
    );
    const result = await this.page.locator("h1").innerText();
    expect(result).toMatch(/dispatched/);
  }

  public async setForcedIp(ip: string) {
    await this.navigateTo("set-forced-ip", `&ip=${ip}`);

    const result = await this.page.locator("h1").innerText();
    expect(result).toMatch(/saved/);
  }

  public async addDecision(
    ip: string,
    type: string,
    origin: string,
    duration: number
  ) {
    await this.navigateTo(
      "add-local-decision",
      `&ip=${ip}&type=${type}&duration=${duration}&origin=${origin}`
    );
    const result = await this.page.locator("h1").innerText();
    expect(result).toMatch(/true/);
  }
}
