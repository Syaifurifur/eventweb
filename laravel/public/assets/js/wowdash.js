'use strict';

// Sidebar submenu collapsible js
document.querySelectorAll(".sidebar-menu .dropdown").forEach(function (dropdown) {
  dropdown.addEventListener("click", function (e) {
    if (e.target.closest(".sidebar-submenu a")) {
      return;
    }
    var item = this;

    // Close all sibling dropdowns
    item.parentNode.querySelectorAll(".dropdown").forEach(function (sibling) {
      if (sibling !== item) {
        var siblingSub = sibling.querySelector(".sidebar-submenu");
        if (siblingSub) siblingSub.style.display = 'none';
        sibling.classList.remove("dropdown-open");
        sibling.classList.remove("open");
      }
    });

    // Toggle the current dropdown
    var submenu = item.querySelector(".sidebar-submenu");
    if (submenu) {
      submenu.style.display = (submenu.style.display === 'block') ? 'none' : 'block';
    }

    item.classList.toggle("dropdown-open");
  });
});

// Toggle sidebar visibility and active class
const sidebarToggle = document.querySelector(".sidebar-toggle");
if (sidebarToggle) {
  sidebarToggle.addEventListener("click", function () {
    this.classList.toggle("active");
    const sidebar = document.querySelector(".sidebar");
    const main = document.querySelector(".dashboard-main");
    if (sidebar) sidebar.classList.toggle("active");
    if (main) main.classList.toggle("active");
  });
}

// Open sidebar in mobile view and add overlay
const sidebarMobileToggle = document.querySelector(".sidebar-mobile-toggle");
if (sidebarMobileToggle) {
  sidebarMobileToggle.addEventListener("click", function () {
    const sidebar = document.querySelector(".sidebar");
    if (sidebar) sidebar.classList.add("sidebar-open");
    document.body.classList.add("overlay-active");
  });
}

// Close sidebar and remove overlay
const sidebarCloseBtn = document.querySelector(".sidebar-close-btn");
if (sidebarCloseBtn) {
  sidebarCloseBtn.addEventListener("click", function () {
    const sidebar = document.querySelector(".sidebar");
    if (sidebar) sidebar.classList.remove("sidebar-open");
    document.body.classList.remove("overlay-active");
  });
}

// Close mobile sidebar when clicking outside on overlay
document.addEventListener("click", function (e) {
  if (document.body.classList.contains("overlay-active")) {
    const sidebar = document.querySelector(".sidebar");
    const mobileToggle = document.querySelector(".sidebar-mobile-toggle");
    if (sidebar && !sidebar.contains(e.target) && (!mobileToggle || !mobileToggle.contains(e.target))) {
      sidebar.classList.remove("sidebar-open");
      document.body.classList.remove("overlay-active");
    }
  }
});

// Keep current page active in sidebar
document.addEventListener("DOMContentLoaded", function () {
  var nk = window.location.href.split('?')[0].replace(/\/+$/, '');
  var links = document.querySelectorAll("ul#sidebar-menu a");
  var hasServerActive = document.querySelector("ul#sidebar-menu a.active-page");

  links.forEach(function (link) {
    var linkHref = (link.href || '').split('?')[0].replace(/\/+$/, '');
    if ((!hasServerActive && linkHref && linkHref === nk) || link.classList.contains("active-page")) {
      link.classList.add("active-page");
      var parent = link.parentElement;
      if (parent) parent.classList.add("active-page");

      while (parent && parent.tagName !== "BODY") {
        if (parent.tagName === "LI" && parent.classList.contains("dropdown")) {
          parent.classList.add("show");
          parent.classList.add("open");
          parent.classList.add("dropdown-open");
          var sub = parent.querySelector(".sidebar-submenu");
          if (sub) sub.style.display = 'block';
        }
        parent = parent.parentElement;
      }
    }
  });

  // Password toggle support for WowDash .toggle-password
  document.querySelectorAll(".toggle-password").forEach(function (toggle) {
    toggle.addEventListener("click", function () {
      this.classList.toggle("ri-eye-off-line");
      var selector = this.getAttribute("data-toggle");
      var input = selector ? document.querySelector(selector) : this.parentElement.querySelector("input");
      if (input) {
        input.setAttribute("type", input.getAttribute("type") === "password" ? "text" : "password");
      }
    });
  });
});

// Light / Dark theme mode (defaults to light unless user selected dark)
if (localStorage.getItem('color-theme') === 'dark') {
  document.documentElement.classList.add('dark');
  document.documentElement.setAttribute('data-theme', 'dark');
} else {
  document.documentElement.classList.remove('dark');
  document.documentElement.setAttribute('data-theme', 'light');
}

var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
var themeToggleBtn = document.getElementById('theme-toggle');

if (themeToggleDarkIcon && themeToggleLightIcon) {
  if (document.documentElement.classList.contains('dark')) {
    themeToggleLightIcon.classList.remove('hidden');
  } else {
    themeToggleDarkIcon.classList.remove('hidden');
  }
}

if (themeToggleBtn && themeToggleDarkIcon && themeToggleLightIcon) {
  themeToggleBtn.addEventListener('click', function () {
    themeToggleDarkIcon.classList.toggle('hidden');
    themeToggleLightIcon.classList.toggle('hidden');

    if (document.documentElement.classList.contains('dark')) {
      document.documentElement.classList.remove('dark');
      document.documentElement.setAttribute('data-theme', 'light');
      localStorage.setItem('color-theme', 'light');
    } else {
      document.documentElement.classList.add('dark');
      document.documentElement.setAttribute('data-theme', 'dark');
      localStorage.setItem('color-theme', 'dark');
    }
  });
}
