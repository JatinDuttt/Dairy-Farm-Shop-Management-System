const { defineConfig } = require('cypress');

module.exports = defineConfig({
  e2e: {
    baseUrl: 'http://localhost/dfsms',
    specPattern: 'cypress/e2e/**/*.cy.js',
    supportFile: false,
    allowCypressEnv: false,
    video: false,
    screenshotOnRunFailure: true,
    defaultCommandTimeout: 8000,
    setupNodeEvents(on, config) {}
  }
});
