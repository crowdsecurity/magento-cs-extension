import { Page } from "@playwright/test";

export default class RunCronPage {
  page: Page;
  url: string;

  constructor(page: Page) {
    this.url = "/cronLaunch.php?job=";
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
  }

  public async refreshCache() {
    await this.navigateTo("CrowdSec\\Engine\\Cron\\RefreshCache");
  }

  public async pruneCache() {
    await this.navigateTo("CrowdSec\\Engine\\Cron\\PruneCache");
  }

  public async pushSignals() {
    await this.navigateTo("CrowdSec\\Engine\\Cron\\PushSignals");
  }

  public async cleanEvents() {
    await this.navigateTo("CrowdSec\\Engine\\Cron\\CleanEvents");
  }
}
