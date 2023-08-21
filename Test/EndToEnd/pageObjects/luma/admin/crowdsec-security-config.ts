import { expect, Page } from "@playwright/test";

export default class CrowdSecSecurityConfigPage {
  page: Page;
  url: string;

  constructor(page: Page) {
    this.url = "/admin/admin/system_config/edit/section/crowdsec_engine/";
    this.page = page;
  }

  public async navigateTo() {
    await this.page.goto(this.url);
    await expect(this.page).toHaveTitle(/Configuration/);
  }

  public async setDefaultConfig() {
    await this.page
      .getByRole("combobox", { name: "[GLOBAL] Environment" })
      .selectOption("dev");

    await this.page
      .getByRole("listbox", { name: "[GLOBAL] List of detection scenarios" })
      .selectOption(["magento2/pages-scan", "magento2/user-enum"]);

    await this.page.getByLabel("Ban duration").fill("14400");

    await this.page
      .getByRole("combobox", {
        name: "[GLOBAL] Ban IP locally",
      })
      .selectOption("1");

    await this.page
      .getByRole("combobox", { name: "[GLOBAL] Block banned IP" })
      .selectOption("1");

    await this.page
      .getByRole("combobox", { name: "[GLOBAL] Technology" })
      .selectOption("phpfs");

    await this.page
      .getByRole("combobox", { name: "[GLOBAL] Log level" })
      .selectOption("100");

    await this.saveConfig();
  }

  public async clearCache() {
    await this.page
      .locator("#crowdsec_engine_decisions_cache_clear_cache")
      .click();
    await expect(this.page.locator("#cache_clearing_result")).toContainText(
      /cache \(.*\) has been cleared/
    );
  }

  public async enroll() {
    await this.page.locator("#crowdsec_engine_general_enroll").click();

    await expect(this.page.locator("#engine_enroll_result")).toContainText(
      "Enroll request successfully sent",
      {
        timeout: 30000,
      }
    );
  }

  public async pushSignals() {
    await this.page.locator("#crowdsec_engine_crons_signals_push").click();
    await expect(this.page.locator("#signals_push_result")).toContainText(
      /pushed signals/,
      {
        timeout: 30000,
      }
    );
  }

  public async refreshCache() {
    await this.page.locator("#crowdsec_engine_crons_cache_refresh").click();
    await expect(this.page.locator("#cache_refresh_result")).toContainText(
      /cache .* has been refreshed/,
      {
        timeout: 30000,
      }
    );
  }

  public async saveConfig(check = true) {
    await this.page.getByRole("button", { name: "Save Config" }).click();
    if (check) {
      await expect(this.page.locator(".message-success")).toContainText(
        /You saved the configuration./
      );
    }
  }
}
