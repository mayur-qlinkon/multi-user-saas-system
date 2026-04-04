# 🚀 AI DEVELOPMENT GUIDELINES — PRODUCTION SAAS (QLINKON)

## 🎯 CORE OBJECTIVE

Build a **production-grade, scalable SaaS ERP system** without breaking existing features.

---

## 🔐 MULTI-TENANT SAFETY (STRICT)

* Every query MUST include `company_id`
* NEVER mix data across companies
* NEVER expose another company's data
* Treat this as **highest priority rule**

---

## 🧠 ARCHITECTURE RULES

* Follow: **Controller → Service → Model**
* ❌ No business logic in Blade
* ❌ No heavy logic in Controllers
* ✅ Reuse existing Services
* ❌ Do NOT duplicate logic

---

## 🗄️ DATABASE SAFETY

* ❌ Do NOT modify schema unless explicitly asked
* ❌ Do NOT drop/change existing columns
* Respect all relationships:

  * `store_user`
  * `role_user`
  * `employee`
* Always handle:

  * null values
  * empty datasets
  * multi-relations

---

## 🔑 PERMISSIONS & SECURITY

* UI ≠ Security
* ALWAYS enforce permissions in backend
* Respect:

  * roles
  * permissions
* NEVER allow unauthorized access

---

## 🧩 SESSION & CONTEXT

* Session is **UI context only**
* ❌ Do NOT trust session blindly
* ALWAYS validate with database
* Auto-fix invalid session values

---

## 🏬 STORE & ROLE LOGIC

* Users may have:

  * multiple stores
  * multiple roles
* NEVER assume single store
* NEVER assume single role
* Always use pivot tables for logic

---

## 🎨 FRONTEND RULES

* Use **Blade + Tailwind only**
* ❌ No Node/npm on production
* Keep UI:

  * responsive (mobile/tablet/desktop)
  * clean
* Do NOT break SPA navigation

---

## ⚡ PERFORMANCE RULES

* ❌ Avoid N+1 queries
* ✅ Use eager loading
* ❌ No heavy queries inside loops
* Use caching when needed

---

## 📢 ANNOUNCEMENT / REAL-TIME FEATURES

* Use async calls (AJAX)
* Do NOT block UI unnecessarily
* Respect targeting logic strictly

---

## 🧪 TESTING & EDGE CASES

Always consider:

* multi-store users
* multi-role users
* empty data
* invalid session
* expired records
* reassigned users

---

## 🧱 CODE QUALITY RULES

* Make **minimal, clean changes**
* ❌ Do NOT rewrite working modules
* Follow existing naming conventions
* Keep code readable and maintainable

---

## 🚫 STRICT RULE

**DO NOT GUESS LOGIC**

* Analyze existing codebase first
* Follow existing patterns
* If unclear → infer from system structure

---

## 🧭 FINAL PRINCIPLE

> Stability > Features
> Safety > Speed
> Consistency > Creativity

---

## ✅ EXECUTION MODE

* Focus ONLY on requested task
* Do NOT over-engineer
* Do NOT refactor unrelated code
* Deliver **safe, production-ready output**
