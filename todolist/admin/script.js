// Web School Admin Dashboard - Shared JavaScript
class AdminDashboard {
  constructor() {
    this.init();
  }

  init() {
    this.setupSidebar();
    this.setupMobileMenu();
    this.setupActiveNavigation();
    this.setupLogout();
    this.setupSearch();
    this.setupTables();
    this.setupForms();
    this.setupModals();
    this.setupNotifications();
    this.fetchPendingAvis();
    this.fetchUsers(); // Call this to load users on page load
  }

  setupSidebar() {
    // Create mobile menu toggle
    const menuToggle = document.createElement("button");
    menuToggle.className = "mobile-menu-toggle";
    menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
    menuToggle.setAttribute("aria-label", "Toggle menu");

    Object.assign(menuToggle.style, {
      position: "fixed",
      top: "20px",
      left: "20px",
      zIndex: "1001",
      fontSize: "1.25rem",
      cursor: "pointer",
      color: "#2563eb",
      backgroundColor: "white",
      padding: "12px",
      borderRadius: "50%",
      boxShadow: "0 4px 6px -1px rgb(0 0 0 / 0.1)",
      width: "48px",
      height: "48px",
      display: "none",
      alignItems: "center",
      justifyContent: "center",
      border: "none",
      transition: "all 0.2s ease",
    });

    document.body.appendChild(menuToggle);

    // Toggle sidebar on mobile
    menuToggle.addEventListener("click", () => {
      const sidebar = document.querySelector(".sidebar");
      sidebar.classList.toggle("active");
      menuToggle.classList.toggle("active");
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener("click", (e) => {
      const sidebar = document.querySelector(".sidebar");
      const menuToggle = document.querySelector(".mobile-menu-toggle");

      if (
        window.innerWidth <= 1024 &&
        !sidebar.contains(e.target) &&
        !menuToggle.contains(e.target) &&
        sidebar.classList.contains("active")
      ) {
        sidebar.classList.remove("active");
        menuToggle.classList.remove("active");
      }
    });

    // Show/hide mobile menu toggle based on screen size
    this.handleResize();
    window.addEventListener("resize", () => this.handleResize());
  }

  handleResize() {
    const menuToggle = document.querySelector(".mobile-menu-toggle");
    const sidebar = document.querySelector(".sidebar");

    if (window.innerWidth <= 1024) {
      menuToggle.style.display = "flex";
      sidebar.classList.remove("active");
    } else {
      menuToggle.style.display = "none";
      sidebar.classList.remove("active");
    }
  }

  setupMobileMenu() {
    // Add mobile menu toggle styles
    const style = document.createElement("style");
    style.textContent = `
            .mobile-menu-toggle.active {
                background-color: #2563eb !important;
                color: white !important;
            }
            
            @media (max-width: 1024px) {
                .sidebar {
                    transform: translateX(-100%);
                }
                .sidebar.active {
                    transform: translateX(0);
                }
                .main-content {
                    margin-left: 0;
                }
            }
        `;
    document.head.appendChild(style);
  }

  setupActiveNavigation() {
    const currentPage =
      window.location.pathname.split("/").pop() || "dashboard.html";
    const menuItems = document.querySelectorAll(".menu-item a");

    menuItems.forEach((link) => {
      const linkPage = link.getAttribute("href");
      if (linkPage === currentPage) {
        link.parentElement.classList.add("active");
      }
    });
  }

  setupLogout() {
    const logoutBtn = document.querySelector(".logout-btn");
    if (logoutBtn) {
      logoutBtn.addEventListener("click", (e) => {
        e.preventDefault();
        this.showConfirmDialog(
          "Déconnexion",
          "Êtes-vous sûr de vouloir vous déconnecter ?",
          () => {
            // Add loading state
            logoutBtn.innerHTML = '<div class="spinner"></div> Déconnexion...';
            logoutBtn.disabled = true;

            // Simulate logout process
            setTimeout(() => {
              window.location.href = "login.html";
            }, 1000);
          }
        );
      });
    }
  }

  setupSearch() {
    const searchInputs = document.querySelectorAll(".search-box input");
    searchInputs.forEach((input) => {
      input.addEventListener("input", (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const table = input
          .closest(".table-container")
          ?.querySelector(".table");

        if (table) {
          this.filterTable(table, searchTerm);
        }
      });
    });
  }

  filterTable(table, searchTerm) {
    const rows = table.querySelectorAll("tbody tr");

    rows.forEach((row) => {
      const text = row.textContent.toLowerCase();
      const isVisible = text.includes(searchTerm);
      row.style.display = isVisible ? "" : "none";
    });
  }

  setupTables() {
    // Add sorting functionality to tables
    const tables = document.querySelectorAll(".table");
    tables.forEach((table) => {
      const headers = table.querySelectorAll("th[data-sortable]");
      headers.forEach((header) => {
        header.style.cursor = "pointer";
        header.addEventListener("click", () => {
          this.sortTable(table, header);
        });
      });
    });

    // Add row selection
    const selectableRows = document.querySelectorAll(
      ".table tbody tr[data-selectable]"
    );
    selectableRows.forEach((row) => {
      row.addEventListener("click", () => {
        row.classList.toggle("selected");
      });
    });
  }

  sortTable(table, header) {
    const column = Array.from(header.parentElement.children).indexOf(header);
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr"));
    const isAscending = header.classList.contains("sort-asc");

    // Remove existing sort classes
    header.parentElement.querySelectorAll("th").forEach((th) => {
      th.classList.remove("sort-asc", "sort-desc");
    });

    // Add sort class
    header.classList.add(isAscending ? "sort-desc" : "sort-asc");

    // Sort rows
    rows.sort((a, b) => {
      const aValue = a.children[column]?.textContent || "";
      const bValue = b.children[column]?.textContent || "";

      if (isAscending) {
        return bValue.localeCompare(aValue);
      } else {
        return aValue.localeCompare(bValue);
      }
    });

    // Reorder rows
    rows.forEach((row) => tbody.appendChild(row));
  }

  setupForms() {
    // Form validation
    const forms = document.querySelectorAll("form[data-validate]");
    forms.forEach((form) => {
      form.addEventListener("submit", (e) => {
        if (!this.validateForm(form)) {
          e.preventDefault();
        }
      });
    });

    // Auto-save functionality
    const autoSaveForms = document.querySelectorAll("form[data-autosave]");
    autoSaveForms.forEach((form) => {
      const inputs = form.querySelectorAll("input, textarea, select");
      inputs.forEach((input) => {
        input.addEventListener("change", () => {
          this.autoSaveForm(form);
        });
      });
    });
  }

  validateForm(form) {
    let isValid = true;
    form
      .querySelectorAll("input[required], select[required], textarea[required]")
      .forEach((field) => {
        if (!field.value.trim()) {
          this.showFieldError(field, "Ce champ est requis.");
          isValid = false;
        } else {
          this.clearFieldError(field);
        }
      });
    return isValid;
  }

  showFieldError(field, message) {
    let errorElement = field.nextElementSibling;
    if (!errorElement || !errorElement.classList.contains("error-message")) {
      errorElement = document.createElement("div");
      errorElement.classList.add("error-message");
      field.parentNode.insertBefore(errorElement, field.nextSibling);
    }
    errorElement.textContent = message;
    field.classList.add("is-invalid");
  }

  clearFieldError(field) {
    const errorElement = field.nextElementSibling;
    if (errorElement && errorElement.classList.contains("error-message")) {
      errorElement.remove();
    }
    field.classList.remove("is-invalid");
  }

  autoSaveForm(form) {
    const formData = new FormData(form);
    const data = {};
    for (const [key, value] of formData.entries()) {
      data[key] = value;
    }
    localStorage.setItem(form.id || form.name, JSON.stringify(data));
    this.showNotification("Formulaire sauvegardé automatiquement.", "info");
  }

  setupModals() {
    document.querySelectorAll("[data-modal-target]").forEach((button) => {
      button.addEventListener("click", () => {
        const modalId = button.dataset.modalTarget;
        this.openModal(modalId);
      });
    });

    document.querySelectorAll("[data-modal-close]").forEach((button) => {
      button.addEventListener("click", () => {
        const modal = button.closest(".modal");
        this.closeModal(modal);
      });
    });

    window.addEventListener("click", (e) => {
      if (e.target.classList.contains("modal")) {
        this.closeModal(e.target);
      }
    });
  }

  openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.style.display = "block";
      setTimeout(() => modal.classList.add("show"), 10);
    }
  }

  closeModal(modal) {
    if (modal) {
      modal.classList.remove("show");
      setTimeout(() => (modal.style.display = "none"), 300);
    }
  }

  setupNotifications() {
    // Dynamically create a notification container if it doesn't exist
    let notificationContainer = document.getElementById(
      "notification-container"
    );
    if (!notificationContainer) {
      notificationContainer = document.createElement("div");
      notificationContainer.id = "notification-container";
      Object.assign(notificationContainer.style, {
        position: "fixed",
        top: "20px",
        right: "20px",
        zIndex: "1050",
        maxWidth: "350px",
      });
      document.body.appendChild(notificationContainer);
    }
  }

  showNotification(message, type = "info", duration = 5000) {
    const notificationContainer = document.getElementById(
      "notification-container"
    );
    if (!notificationContainer) return;

    const notification = document.createElement("div");
    notification.classList.add("notification", `notification-${type}`);
    notification.innerHTML = `
            <div class="notification-content">${message}</div>
            <button class="notification-close">&times;</button>
        `;

    notificationContainer.appendChild(notification);

    // Animate in
    setTimeout(() => notification.classList.add("show"), 10);

    // Auto-remove
    const timeoutId = setTimeout(() => {
      notification.classList.remove("show");
      notification.addEventListener(
        "transitionend",
        () => notification.remove(),
        { once: true }
      );
    }, duration);

    // Manual close
    notification
      .querySelector(".notification-close")
      .addEventListener("click", () => {
        clearTimeout(timeoutId);
        notification.classList.remove("show");
        notification.addEventListener(
          "transitionend",
          () => notification.remove(),
          { once: true }
        );
      });
  }

  showConfirmDialog(title, message, onConfirm) {
    let dialog = document.getElementById("confirmDialog");
    if (!dialog) {
      dialog = document.createElement("div");
      dialog.id = "confirmDialog";
      dialog.classList.add("modal");
      dialog.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="close" data-modal-close="#confirmDialog">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-modal-close="#confirmDialog">Annuler</button>
                        <button type="button" class="btn btn-primary" id="confirmActionBtn">Confirmer</button>
                    </div>
                </div>
            `;
      document.body.appendChild(dialog);
    }

    dialog.querySelector(".modal-title").textContent = title;
    dialog.querySelector(".modal-body p").textContent = message;
    const confirmActionBtn = document.getElementById("confirmActionBtn");

    const confirmHandler = () => {
      onConfirm();
      this.closeModal(dialog);
      confirmActionBtn.removeEventListener("click", confirmHandler);
    };

    confirmActionBtn.addEventListener("click", confirmHandler);
    this.openModal("confirmDialog");
  }

  formatDate(date) {
    const options = { year: "numeric", month: "long", day: "numeric" };
    return new Date(date).toLocaleDateString("fr-FR", options);
  }

  formatNumber(number) {
    return new Intl.NumberFormat("fr-FR").format(number);
  }

  debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  }

  fetchPendingAvis() {
    const loadingAvis = document.getElementById("loading-avis");
    const emptyAvisState = document.getElementById("empty-avis-state");
    const pendingAvisTableBody = document.querySelector(
      "#pendingAvisTable tbody"
    );

    if (!pendingAvisTableBody) {
      console.error("Pending Avis table body not found.");
      return;
    }

    // Clear only non-loading/empty state rows to prepare for new data
    Array.from(pendingAvisTableBody.children).forEach((child) => {
      if (
        child.id !== "loading-avis-row" &&
        child.id !== "empty-avis-state-row"
      ) {
        child.remove();
      }
    });

    const loadingRow =
      document.getElementById("loading-avis-row") ||
      pendingAvisTableBody.querySelector("#loading-avis")?.closest("tr");
    const emptyStateRow =
      document.getElementById("empty-avis-state-row") ||
      pendingAvisTableBody.querySelector("#empty-avis-state")?.closest("tr");

    if (loadingRow) loadingRow.style.display = "table-row";
    if (emptyStateRow) emptyStateRow.style.display = "none";

    fetch("get_pending_avis.php") // Will create this file next
      .then((response) => response.json())
      .then((data) => {
        if (loadingRow) loadingRow.style.display = "none";

        if (data.success && data.avis.length > 0) {
          data.avis.forEach((avis) => {
            const row = pendingAvisTableBody.insertRow();
            row.innerHTML = `
                        <td>${avis.titre}</td>
                        <td>${avis.contenu.substring(0, 70)}${
              avis.contenu.length > 70 ? "..." : ""
            }</td>
                        <td>${avis.auteur}</td>
                        <td>${this.formatDate(avis.date_creation)}</td>
                        <td><span class="badge bg-warning">${
                          avis.statut
                        }</span></td>
                        <td>
                            <button class="btn btn-sm btn-success mark-read-btn" data-id="${
                              avis.id_avis
                            }" title="Marquer comme lu"><i class="fas fa-check"></i></button>
                            <button class="btn btn-sm btn-danger delete-avis-btn" data-id="${
                              avis.id_avis
                            }" title="Supprimer"><i class="fas fa-trash-alt"></i></button>
                        </td>
                    `;
          });
          this.setupAvisActionButtons();
        } else {
          if (emptyStateRow) emptyStateRow.style.display = "table-row";
        }
      })
      .catch((error) => {
        console.error("Error fetching pending avis:", error);
        if (loadingRow) loadingRow.style.display = "none";
        if (emptyStateRow) emptyStateRow.style.display = "table-row";
        if (emptyStateRow)
          emptyStateRow.innerHTML =
            '<td colspan="6" class="text-center"><p class="text-danger">Erreur lors du chargement des avis. Veuillez réessayer.</p></td>';
      });
  }

  setupAvisActionButtons() {
    document.querySelectorAll(".mark-read-btn").forEach((button) => {
      button.addEventListener("click", (e) => {
        const avisId = e.currentTarget.dataset.id;
        this.markAvisAsRead(avisId);
      });
    });

    document.querySelectorAll(".delete-avis-btn").forEach((button) => {
      button.addEventListener("click", (e) => {
        const avisId = e.currentTarget.dataset.id;
        this.deleteAvis(avisId);
      });
    });
  }

  markAvisAsRead(avisId) {
    fetch("update_avis_status.php", {
      // Will create this file next
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `id_avis=${avisId}&status=read`,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          this.showNotification("Avis marqué comme lu.", "success");
          this.fetchPendingAvis(); // Refresh the list
        } else {
          this.showNotification("Erreur: " + data.message, "danger");
        }
      })
      .catch((error) => {
        console.error("Error marking avis as read:", error);
        this.showNotification(
          "Erreur réseau lors de la mise à jour de l'avis.",
          "danger"
        );
      });
  }

  deleteAvis(avisId) {
    this.showConfirmDialog(
      "Supprimer l'avis",
      "Êtes-vous sûr de vouloir supprimer cet avis ?",
      () => {
        fetch("delete_avis.php", {
          // Will create this file next
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `id_avis=${avisId}`,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              this.showNotification("Avis supprimé avec succès.", "success");
              this.fetchPendingAvis(); // Refresh the list
            } else {
              this.showNotification("Erreur: " + data.message, "danger");
            }
          })
          .catch((error) => {
            console.error("Error deleting avis:", error);
            this.showNotification(
              "Erreur réseau lors de la suppression de l'avis.",
              "danger"
            );
          });
      }
    );
  }

  fetchUsers() {
    const usersTableBody = document.querySelector("#usersTable tbody");
    const loadingIndicator = document.getElementById("loading-users");
    const emptyState = document.getElementById("empty-users-state");

    if (!usersTableBody || !loadingIndicator || !emptyState) {
      console.error("Required elements for users table not found.");
      return;
    }

    usersTableBody.innerHTML = ""; // Clear existing content
    loadingIndicator.style.display = "block";
    emptyState.style.display = "none";

    fetch("get_users.php") // Relative path from admin/comptes.html
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => {
        loadingIndicator.style.display = "none";
        if (data.success && data.users.length > 0) {
          data.users.forEach((user) => {
            const row = document.createElement("tr");
            const statusClass =
              user.est_active == 1 ? "badge-success" : "badge-danger";
            const statusText = user.est_active == 1 ? "Actif" : "Inactif";
            const buttonText = user.est_active == 1 ? "Désactiver" : "Activer";
            const buttonClass =
              user.est_active == 1 ? "btn-warning" : "btn-success";

            row.innerHTML = `
                        <td>${user.id_utilisateur}</td>
                        <td>${user.nom_utilisateur} ${user.prenom_utilisateur}</td>
                        <td>${user.email}</td>
                        <td><span class="badge badge-info">${user.nom_role}</span></td>
                        <td><span class="badge ${statusClass}">${statusText}</span></td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm ${buttonClass}" data-id="${user.id_utilisateur}" data-status="${user.est_active}">${buttonText}</button>
                            </div>
                        </td>
                    `;
            usersTableBody.appendChild(row);
          });
          this.setupUserActionButtons(); // Setup event listeners for new buttons
        } else {
          emptyState.style.display = "block";
        }
      })
      .catch((error) => {
        console.error("Error fetching users:", error);
        loadingIndicator.style.display = "none";
        emptyState.style.display = "block";
        emptyState.querySelector("p").textContent =
          "Erreur lors du chargement des utilisateurs.";
        this.showNotification(
          "Erreur lors du chargement des utilisateurs.",
          "danger"
        );
      });
  }

  setupUserActionButtons() {
    const actionButtons = document.querySelectorAll(
      "#usersTable .btn[data-id]"
    );
    actionButtons.forEach((button) => {
      button.addEventListener("click", (event) => {
        const userId = event.target.dataset.id;
        const currentStatus = event.target.dataset.status;
        const newStatus = currentStatus == 1 ? 0 : 1; // Toggle status
        this.toggleUserStatus(userId, newStatus);
      });
    });
  }

  toggleUserStatus(userId, newStatus) {
    this.showConfirmDialog(
      newStatus === 1 ? "Activer le compte" : "Désactiver le compte",
      `Êtes-vous sûr de vouloir ${
        newStatus === 1 ? "activer" : "désactiver"
      } ce compte utilisateur ?`,
      () => {
        fetch("update_user_status.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `user_id=${userId}&new_status=${newStatus}`,
        })
          .then((response) => {
            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
          })
          .then((data) => {
            if (data.success) {
              this.showNotification(data.message, "success");
              this.fetchUsers(); // Refresh the user list
            } else {
              this.showNotification(`Erreur: ${data.message}`, "danger");
            }
          })
          .catch((error) => {
            console.error("Error toggling user status:", error);
            this.showNotification(
              "Erreur réseau lors de la mise à jour du statut.",
              "danger"
            );
          });
      }
    );
  }
}

// Initialize the dashboard
document.addEventListener("DOMContentLoaded", () => {
  new AdminDashboard();
});
