# AVis - Project Structure

This project is built on a custom, secure **Action-Domain-Responder (ADR)** architecture with a stateless **Dual-Token JWT** authentication system.

To maintain the integrity of the project, it is essential to adhere strictly to the separation of concerns outlined below.

---

## 📂 Directory Structure

```text
AVis/
├── public/               # The Website Root 
│   ├── css/              # Stylesheets
│   ├── img/              # Public assets
│   ├── js/               
│   │   ├── api/          # Network communication with the backend APIs
│   │   └── ui/           # DOM manipulation & event listeners
│   ├── .htaccess         # Forwards all traffic to index.php
│   └── index.php         # The Front Controller, routes all requests to their appropiate Actions
│
├── src/                  # The Backend Logic 
│   ├── Actions/          # Route Handlers (API and View)
│   ├── Domain/           # Business Logic & Database Operations
│   ├── Responders/       # Output Formatters (JSON, HTML, Redirects)
│   └── Core/             # Engine Mechanics (Router, Database, JWT Auth)
│
├── views/                # The Frontend Designs 
│   ├── components/       # Reusable chunks (Navbars, Modals)
│   ├── layouts/          # Master HTML structures
│   └── pages/            # Specific view content injected into layouts
│
├── .env                  # Environment variables 
└── config.php            # Global configurations
```
--- 
## 📜 Rules of Engagement: What Goes Where?

### 1. The `public/` Directory
**Rule:** The Apache server can only run/send files from this folder. `index.php` is the entry point and calls the necessary actions.
* `ROOT` is a constant created at the top of `index.php`, it dynamically contains the project root folder and should be used for any project files linked in **PHP** (in Html, link files using their relative path in the public folder).
* All **JavaScript** code from the project must be in `/js`
* JavaScript files in `/js/api` should only handle APIs and should only be implemented using the `ApiHandler.js` wrapper  (with the sole exception of `AuthApi.js`, of course)
* Only JavaScript files in `/js/ui` should handle the **DOM**

### 2. The `src/Actions/` Directory
**Rule:** Every single route in `index.php` points to exactly *one* Action class. Actions act as the central point of the whole arhitecture, all other components are connected to them. Only one action is called per http request .
* **API Actions (`src/Actions/api/`)**: Catch raw JSON input (`file_get_contents('php://input')`), pass it to a **Domain** class, and uses the `JsonResponder` to send a response.
* **Page Actions (`src/Actions/page/`)**: Uses the `HtmlResponder` to drop a specific layout and page template. Page Actions should **never** interact with **Domains**.
    * For pages that require an account, the Page Actions should verify **cookies** for the refresh token.
* **Restriction:** Actions must **never** contain SQL queries. They must rely on the Domain layer for data.
    * Actions should utilize `try/catch` blocks around the **Domain** interactions to prevent unhandled PDOexceptions.


### 3. The `src/Domain/` Directory
**Rule:** Anything that interacts with the database should be in Domain. This layer executes SQL queries, hashes passwords, and enforces business rules.
* The only external file a Domain should use is `Database.php`.
* Domains are grouped by resource not by specific actions.
* **Restriction:** The Domain layer must never send HTTP responses. It strictly returns raw data arrays or booleans back to the Action.

### 4. The `src/Responders/` Directory
**Rule:** Responders are the only files authorized to speak to the browser.
* If you need to send data back, instantiate the correct Responder inside your Action.
* `JsonResponder`: Wraps data in a strict `{"success": true, "data": {...}}` or `{"success": false, "error": string}` envelope.
* `HtmlResponder`: Injects `$pageTemplate` into the correct layout.
* `RedirectResponder`:  Redirects the browser to a different URL.
* `ErrorResponder`: It sends an error page to the browser, it should be used like an exception caught and handled by the browser (APIs should still use `JsonResponder` for errors).

### 5. The 'src/Core/' Directory
**Rule:** This contains core **utilities** to the project structure.
* *Only* files that are *essential* for the *whole* website to function should be here
* It should already contain all necessary files.

### 6. The `views/` Directory
**Rule:** Keep logic to an absolute minimum here.
* **Layouts (`views/layouts/`)**: The complete HTML `<html>...</html>` structure. Include your `<script>` and `<link>` tags here using **absolute paths** (e.g., `/js/api/ApiHandler.js`).
* **Pages (`views/pages/`)**: Fragments of HTML (e.g., just the `<form>` or `<h1>`) that get injected into the layout's `<main>` tag.
* **Components (`views/components/`)**: This should contain UI components that get repeated in **multiple pages** and that need to **remain consistent**. They can be used in both **layouts** and **pages**.
* **Restriction:** Do not write complex PHP data-fetching logic in views. The Action should fetch the data via the Domain, and pass it to the Responder, which extracts it for the View.

---

## 🔐 Security Protocol: Dual-Token JWT

This application does not use PHP Sessions. It uses a highly volatile, stateless Dual-Token architecture:

1.  **Refresh Token:** Valid for around **30 days**. Stored in a secure, JavaScript-invisible `HttpOnly` cookie. Used automatically by the browser to authorize HTML **page Views** and to request new **Access Tokens**.
2.  **Access Token:** Valid for around **10 minutes**. Stored strictly in JavaScript memory and attached as a `Bearer` token to the `Authorization` header for all backend API calls (by `ApiHandler.js`). Make sure all protected APIs verify for the access token.

---

## ❓ How to Add a New Feature

**Scenario: You need a new API endpoint to fetch Map Data.**
1.  **Domain:** Add a method to your Domain class (or create `MapDomain.php`) that executes the `SELECT` query.
2.  **Action:** Create `GetMapDataAction.php`. Catch the request, verify the JWT, call the Domain, and pass the result to `JsonResponder`.
3.  **Route:** Register the route in `public/index.php`: `$router->addRoute('GET', '/api/map', new GetMapDataAction());`.
4.  **Frontend API:** Add a fetch method to `js/api/MapApi.js`.
5.  **Frontend UI:** Hook a button click in `js/ui/mapDom.js` to call `MapApi.js` and render the result on the screen.