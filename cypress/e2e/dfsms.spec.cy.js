// Dairy Farm Shop Management System - Cypress E2E Tests
// Run: npx cypress open  (then select this spec)
// Or: npx cypress run --browser chrome --spec cypress/e2e/dfsms.spec.cy.js

const BASE = 'http://localhost/dfsms';

function login() {
  cy.visit(BASE + '/index.php');
  cy.get('#username').type('admin');
  cy.get('#password').type('admin123');
  cy.get('#login-btn').click();
}

describe('DFSMS - Login & Authentication', () => {
  beforeEach(() => {
    cy.visit(BASE + '/index.php');
  });

  it('shows the login page with correct title', () => {
    cy.title().should('include', 'Dairy Farm Shop Management System');
    cy.get('h1').should('contain', 'Dairy Farm');
  });

  it('rejects invalid credentials', () => {
    cy.get('#username').type('wronguser');
    cy.get('#password').type('wrongpass');
    cy.get('#login-btn').click();
    cy.get('.alert-error').should('be.visible');
  });

  it('logs in with valid credentials', () => {
    cy.get('#username').type('admin');
    cy.get('#password').type('admin123');
    cy.get('#login-btn').click();
    cy.url().should('include', 'dashboard.php');
  });
});

describe('DFSMS - Customer Authentication', () => {
  const email = `customer${Date.now()}@example.com`;

  it('registers and logs out a new customer', () => {
    cy.visit(BASE + '/customer-register.php');
    cy.get('input[name="full_name"]').type('Test Customer');
    cy.get('input[name="email"]').type(email);
    cy.get('input[name="mobile"]').type('9876543210');
    cy.get('input[name="password"]').type('secret123');
    cy.get('input[name="confirm_password"]').type('secret123');
    cy.contains('button', 'Register').click();
    cy.url().should('include', 'customer-dashboard.php');
    cy.contains('Welcome, Test Customer').should('be.visible');
    cy.contains('Logout').click();
    cy.url().should('include', 'customer-login.php');
  });

  it('logs in an existing customer', () => {
    cy.visit(BASE + '/customer-login.php');
    cy.get('input[name="email"]').type(email);
    cy.get('input[name="password"]').type('secret123');
    cy.contains('button', 'Login').click();
    cy.url().should('include', 'customer-dashboard.php');
    cy.get('.product-grid').should('be.visible');
  });

  it('adds a product to cart and places an order', () => {
    cy.visit(BASE + '/customer-login.php');
    cy.get('input[name="email"]').type(email);
    cy.get('input[name="password"]').type('secret123');
    cy.contains('button', 'Login').click();
    cy.contains('button', 'Add to cart').first().click();
    cy.get('#cart-count', { timeout: 10000 }).invoke('text').should('match', /\([1-9][0-9]*\)/);
    cy.contains('Cart').click();
    cy.url().should('include', 'cart.php');
    cy.get('input[name="contact"]').type('9876543210');
    cy.get('select[name="payment_mode"]').select('Cash');
    cy.contains('button', 'Place order').click();
    cy.url().should('include', 'view-invoice.php');
    cy.contains('Order placed successfully').should('be.visible');
  });
});

describe('DFSMS - Dashboard', () => {
  beforeEach(() => {
    login();
  });

  it('shows stat cards on dashboard', () => {
    cy.get('.stats-grid').should('be.visible');
    cy.get('.stat-card').should('have.length', 4);
  });

  it('shows quick action links', () => {
    cy.get('.quick-card').should('have.length.greaterThan', 3);
  });
});

describe('DFSMS - Products', () => {
  beforeEach(() => {
    login();
    cy.visit(BASE + '/manage-products.php');
  });

  it('displays the products table', () => {
    cy.get('#products-table').should('be.visible');
    cy.get('#products-table tbody tr').should('have.length.greaterThan', 0);
  });

  it('navigates to add and edit product forms', () => {
    cy.contains('Add product').click();
    cy.url().should('include', 'add-product.php');
    cy.get('#add-product-form').should('be.visible');

    cy.visit(BASE + '/manage-products.php');
    cy.contains('Edit').first().click();
    cy.url().should('include', 'edit-product.php');
    cy.get('#edit-product-form').should('be.visible');
  });

  it('fills and submits the add product form', () => {
    cy.visit(BASE + '/add-product.php');
    cy.get('#product-name').type('Test Milk 500ml');
    cy.get('#category').select(1);
    cy.get('#company').select(1);
    cy.get('#price').type('45');
    cy.get('[type=submit]').click();
    cy.url().should('include', 'manage-products.php');
    cy.get('.alert-success').should('be.visible');
  });
});

describe('DFSMS - Categories', () => {
  beforeEach(() => {
    login();
    cy.visit(BASE + '/manage-categories.php');
  });

  it('shows the categories table', () => {
    cy.get('#categories-table').should('be.visible');
  });

  it('adds a new category', () => {
    cy.get('#cat-name').type('Test Category');
    cy.get('#cat-code').type('TEST');
    cy.contains('button', 'Add').click();
    cy.get('.alert-success').should('be.visible');
  });
});

describe('DFSMS - Invoice', () => {
  beforeEach(() => {
    login();
    cy.visit(BASE + '/invoice.php');
  });

  it('shows the invoice form', () => {
    cy.get('#invoice-form').should('be.visible');
    cy.get('#customer-name').should('exist');
    cy.get('#payment-mode').should('exist');
  });

  it('generates an invoice', () => {
    cy.get('#customer-name').type('Test Customer');
    cy.get('#contact').type('9876543210');
    cy.get('#payment-mode').select('Cash');
    cy.get('.product-select').first().select(1);
    cy.get('#generate-invoice').click();
    cy.url().should('include', 'view-invoice.php');
  });
});

describe('DFSMS - Logout', () => {
  it('logs out and redirects to login', () => {
    login();
    cy.visit(BASE + '/logout.php');
    cy.url().should('include', 'index.php');
  });
});
