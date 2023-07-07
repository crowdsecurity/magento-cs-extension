// @ts-check
import { test, expect } from "../fixtures";

test("should retrieve IP", async ({ runActionsPage }) => {
  const result = await runActionsPage.getIp();

  expect(result).toMatch(/^((25[0-5]|(2[0-4]|1\d|[1-9]|)\d)\.?\b){4}$/);
});
