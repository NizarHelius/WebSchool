// Web School Admin Dashboard - Shared JavaScript
// AdminDashboard script version: 2023-11-20_Final_Module_Fix
class AdminDashboard {
  constructor() {
    console.log("AdminDashboard: Constructor called."); // Debugging: Check if constructor is called
    this.init();
  }

  init() {
    console.log("AdminDashboard: init() called."); // Debugging: Check if init() is called
    this.setupSidebar();
    this.handleResize();
    this.setupMobileMenu();
    this.setupActiveNavigation();
    this.setupLogout();
    this.setupSearch();
    this.setupTables();
    this.setupForms();
    this.setupModals();
    this.setupNotifications();
    this.fetchPendingAvis();
    this.setupAvisActionButtons();

    // Page-specific initializations
    if (window.location.pathname.endsWith("comptes.html")) {
      this.fetchUsers();
      this.setupAddUserForm();
      this.setupUserActionButtons();
    }
    if (window.location.pathname.endsWith("etudiants.html")) {
      this.fetchStudents();
      this.setupArchiveStudentButtons();
      this.setupAddStudentForm();
    }
    // Debugging path
    console.log("Current pathname for init:", window.location.pathname);
    console.log(
      "Checking for programmes.html:",
      window.location.pathname.endsWith("programmes.html")
    );

    if (window.location.pathname.endsWith("programmes.html")) {
      this.fetchModules();
      this.setupAddModuleForm();
      this.setupModuleActionButtons();
    }
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
    const logoutBtn = document.getElementById("logoutAdminButton");
    if (logoutBtn) {
      console.log("Logout button found."); // Debugging: check if button is found
      logoutBtn.addEventListener("click", (e) => {
        e.preventDefault();
        console.log("Logout button clicked!"); // Debugging: check if click is registered

        // Temporarily bypass confirm dialog for testing
        window.location.href = "/todolist/logout.php"; // Direct redirect for testing

        // The original code with confirm dialog (commented out for now):
        // this.showConfirmDialog(
        //   "Déconnexion",
        //   "Êtes-vous sûr de vouloir vous déconnecter ?",
        //   () => {
        //     logoutBtn.innerHTML = '<div class="spinner"></div> Déconnexion...';
        //     logoutBtn.disabled = true;
        //     window.location.href = "/todolist/logout.php";
        //     setTimeout(() => {
        //       window.location.href = "/todolist/login.php";
        //     }, 500);
        //   }
        // );
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
    const requiredFields = form.querySelectorAll("[required]");

    requiredFields.forEach((field) => {
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
      errorElement = document.createElement("p");
      errorElement.className = "error-message text-danger mt-1 text-sm";
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
    const formId = form.id;
    for (const [key, value] of formData.entries()) {
      localStorage.setItem(`${formId}_${key}`, value);
    }
    this.showNotification(
      "Données sauvegardées automatiquement.",
      "info",
      2000
    );
  }

  setupModals() {
    document.querySelectorAll("[data-modal-open]").forEach((button) => {
      console.log("setupModals: Found button with data-modal-open:", button); // Debugging: Confirm button is found
      button.addEventListener("click", (e) => {
        e.preventDefault(); // Prevent default action (e.g., SVG lookup if i tag is clicked)
        const modalId = button.dataset.modalOpen;
        console.log("setupModals: Clicked button to open modal:", modalId); // Debugging: Confirm click event
        this.openModal(modalId);
      });
    });

    document.querySelectorAll("[data-modal-close]").forEach((button) => {
      button.addEventListener("click", () => {
        const modalId = button.dataset.modalClose;
        const modal = document.getElementById(modalId.replace("#", ""));
        this.closeModal(modal);
      });
    });
  }

  openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
      modal.style.display = "flex"; // Use flex to center content
      setTimeout(() => modal.classList.add("show"), 10); // For fade-in effect
    }
  }

  closeModal(modal) {
    if (modal) {
      modal.classList.remove("show");
      setTimeout(() => (modal.style.display = "none"), 300); // Wait for fade-out
    }
  }

  setupNotifications() {
    const notificationContainer = document.createElement("div");
    notificationContainer.id = "notification-container";
    Object.assign(notificationContainer.style, {
      position: "fixed",
      top: "20px",
      right: "20px",
      zIndex: "1000",
    });
    document.body.appendChild(notificationContainer);
  }

  showNotification(message, type = "info", duration = 5000) {
    const container = document.getElementById("notification-container");
    if (!container) {
      console.error("Notification container not found.");
      return;
    }

    const notification = document.createElement("div");
    notification.className = `notification ${type} fade-in`;
    notification.innerHTML = `
            <div class="notification-icon">
                ${
                  type === "success"
                    ? '<i class="fas fa-check-circle"></i>'
                    : type === "error"
                    ? '<i class="fas fa-times-circle"></i>'
                    : type === "warning"
                    ? '<i class="fas fa-exclamation-triangle"></i>'
                    : '<i class="fas fa-info-circle"></i>'
                }
            </div>
            <div class="notification-content">
                <p class="notification-message">${message}</p>
                <button class="notification-close" aria-label="Close notification">&times;</button>
            </div>
        `;

    container.appendChild(notification);

    // Auto-remove
    const timeoutId = setTimeout(() => {
      notification.classList.remove("fade-in");
      notification.classList.add("fade-out");
      notification.addEventListener("transitionend", () =>
        notification.remove()
      );
    }, duration);

    // Manual close
    notification
      .querySelector(".notification-close")
      .addEventListener("click", () => {
        clearTimeout(timeoutId);
        notification.classList.remove("fade-in");
        notification.classList.add("fade-out");
        notification.addEventListener("transitionend", () =>
          notification.remove()
        );
      });
  }

  showConfirmDialog(title, message, onConfirm) {
    const dialogId = "confirmDialog";
    let dialog = document.getElementById(dialogId);

    if (!dialog) {
      dialog = document.createElement("div");
      dialog.id = dialogId;
      dialog.className = "modal-backdrop"; // Use modal-backdrop for consistency
      dialog.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="close-button" data-modal-close="${dialogId}">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary close-button" data-modal-close="${dialogId}">Annuler</button>
                        <button type="button" class="btn btn-primary" id="confirmActionBtn">Confirmer</button>
                    </div>
                </div>
            `;
      document.body.appendChild(dialog);

      // Attach close listeners
      dialog.querySelectorAll(".close-button").forEach((btn) => {
        btn.addEventListener("click", () => this.closeModal(dialog));
      });

      // Close on outside click
      dialog.addEventListener("click", (e) => {
        if (e.target === dialog) {
          this.closeModal(dialog);
        }
      });
    } else {
      dialog.querySelector(".modal-title").textContent = title;
      dialog.querySelector(".modal-body p").textContent = message;
    }

    const confirmActionBtn = dialog.querySelector("#confirmActionBtn");

    // Remove all existing event listeners to prevent multiple calls
    const oldConfirmActionBtn = confirmActionBtn;
    const newConfirmActionBtn = oldConfirmActionBtn.cloneNode(true);
    oldConfirmActionBtn.parentNode.replaceChild(
      newConfirmActionBtn,
      oldConfirmActionBtn
    );

    newConfirmActionBtn.addEventListener("click", async () => {
      // Temporary: Confirmation button clicked, directly executing onConfirm callback.
      try {
        await onConfirm(); // Directly execute onConfirm
        // No need to close modal here, it will be closed after the test
        this.showNotification("Action confirmée.", "info");
        this.closeModal(dialog); // Close after action
      } catch (error) {
        console.error("Error in onConfirm callback:", error);
        this.showNotification(
          "Une erreur est survenue lors de l'action.",
          "error"
        );
      }
    });

    this.openModal(dialogId);
  }

  formatDate(date) {
    return new Date(date).toLocaleDateString("fr-FR");
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

  async fetchPendingAvis() {
    const pendingAvisCountElement =
      document.getElementById("pending-avis-count");
    const avisList = document.getElementById("pending-avis-list");
    const loadingIndicator = document.getElementById("loading-avis");
    const emptyState = document.getElementById("empty-avis-state");

    if (!avisList) {
      console.log("Pending Avis table body not found.");
      return; // Only run if on notifications.html
    }

    avisList.style.display = "none";
    if (loadingIndicator) loadingIndicator.style.display = "block";
    if (emptyState) emptyState.style.display = "none";

    try {
      const response = await fetch("./get_pending_avis.php");
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();

      if (loadingIndicator) loadingIndicator.style.display = "none";
      avisList.style.display = "block";

      if (data.success && data.avis.length > 0) {
        avisList.innerHTML = ""; // Clear existing entries
        data.avis.forEach((avis) => {
          const row = `
                        <tr>
                            <td>${avis.id_avis}</td>
                            <td>${avis.avis_content}</td>
                            <td>${avis.avis_date}</td>
                            <td>
                                <span class="badge ${
                                  avis.avis_status === "pending"
                                    ? "badge-warning"
                                    : "badge-success"
                                }">${avis.avis_status}</span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline btn-sm mark-read-btn" data-avis-id="${
                                      avis.id_avis
                                    }"><i class="fas fa-check"></i> Marquer lu</button>
                                    <button class="btn btn-danger btn-sm delete-avis-btn" data-avis-id="${
                                      avis.id_avis
                                    }"><i class="fas fa-trash"></i> Supprimer</button>
                                </div>
                            </td>
                        </tr>
                    `;
          avisList.insertAdjacentHTML("beforeend", row);
        });
        this.setupAvisActionButtons();
        if (pendingAvisCountElement) {
          pendingAvisCountElement.textContent = `(${data.avis.length})`;
        }
      } else {
        emptyState.style.display = "block";
        if (pendingAvisCountElement) {
          pendingAvisCountElement.textContent = "(0)";
        }
      }
    } catch (error) {
      console.error("Error fetching pending avis:", error);
      if (loadingIndicator) loadingIndicator.style.display = "none";
      if (emptyState) emptyState.style.display = "block";
      this.showNotification("Erreur lors du chargement des avis.", "error");
    }
  }

  setupAvisActionButtons() {
    document.querySelectorAll(".mark-read-btn").forEach((button) => {
      button.addEventListener("click", (e) => {
        const avisId = e.currentTarget.dataset.avisId;
        this.markAvisAsRead(avisId);
      });
    });

    document.querySelectorAll(".delete-avis-btn").forEach((button) => {
      button.addEventListener("click", (e) => {
        const avisId = e.currentTarget.dataset.avisId;
        this.deleteAvis(avisId);
      });
    });
  }

  async markAvisAsRead(avisId) {
    try {
      const response = await fetch("./update_avis_status.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `id_avis=${encodeURIComponent(avisId)}&status=read`,
      });
      const result = await response.json();

      if (result.success) {
        this.showNotification("Avis marqué comme lu.", "success");
        this.fetchPendingAvis(); // Refresh the list
      } else {
        this.showNotification("Erreur: " + result.message, "error");
      }
    } catch (error) {
      console.error("Error marking avis as read:", error);
      this.showNotification(
        "Erreur de connexion lors de la mise à jour de l'avis.",
        "error"
      );
    }
  }

  async deleteAvis(avisId) {
    this.showConfirmDialog(
      "Supprimer l'avis",
      "Êtes-vous sûr de vouloir supprimer cet avis ? Cette action est irréversible.",
      async () => {
        try {
          const response = await fetch("./delete_avis.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `id_avis=${encodeURIComponent(avisId)}`,
          });
          const result = await response.json();

          if (result.success) {
            this.showNotification("Avis supprimé avec succès.", "success");
            this.fetchPendingAvis(); // Refresh the list
          } else {
            this.showNotification("Erreur: " + result.message, "error");
          }
        } catch (error) {
          console.error("Error deleting avis:", error);
          this.showNotification(
            "Erreur de connexion lors de la suppression de l'avis.",
            "error"
          );
        }
      }
    );
  }

  async fetchUsers() {
    console.log("Fetching users...");
    const usersTableBody = document.getElementById("usersTableBody");
    const loadingIndicator = document.getElementById("loading-users");
    const emptyState = document.getElementById("empty-users-state");

    if (!usersTableBody) {
      console.log("Required elements for users table not found.");
      return; // Only run if on comptes.html
    }

    usersTableBody.innerHTML = ""; // Clear existing users
    if (loadingIndicator) loadingIndicator.style.display = "block";
    if (emptyState) emptyState.style.display = "none";

    try {
      const response = await fetch("./get_users.php");
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();

      if (loadingIndicator) loadingIndicator.style.display = "none";

      if (data.success && data.users.length > 0) {
        data.users.forEach((user) => {
          const statusText = user.est_actif == 1 ? "Actif" : "Inactif";
          const statusClass =
            user.est_actif == 1 ? "badge-success" : "badge-danger";
          const actionButtonText =
            user.est_actif == 1 ? "Désactiver" : "Activer";
          const actionButtonClass =
            user.est_actif == 1 ? "btn-warning" : "btn-success";

          const row = `
                        <tr>
                            <td>${user.email}</td>
                            <td>${
                              user.nom_complet || user.nom_utilisateur || "N/A"
                            }</td>
                            <td>${user.role_name}</td>
                            <td><span class="badge ${statusClass}">${statusText}</span></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline btn-sm edit-user-btn" data-user-id="${
                                      user.id_utilisateur
                                    }"><i class="fas fa-edit"></i></button>
                                    <button class="btn ${actionButtonClass} btn-sm toggle-status-btn" data-user-id="${
            user.id_utilisateur
          }" data-current-status="${
            user.est_actif
          }"><i class="fas fa-toggle-on"></i> ${actionButtonText}</button>
                                    <button class="btn btn-danger btn-sm delete-user-btn" data-user-id="${
                                      user.id_utilisateur
                                    }"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    `;
          usersTableBody.insertAdjacentHTML("beforeend", row);
        });
        this.setupUserActionButtons(); // Attach event listeners after rendering
      } else {
        if (emptyState) emptyState.style.display = "block";
      }
    } catch (error) {
      console.error("Error fetching users:", error);
      if (loadingIndicator) loadingIndicator.style.display = "none";
      if (emptyState) emptyState.style.display = "block";
      this.showNotification(
        "Erreur lors du chargement des utilisateurs.",
        "error"
      );
    }
  }

  setupUserActionButtons() {
    document.querySelectorAll(".toggle-status-btn").forEach((button) => {
      button.addEventListener("click", (e) => {
        const userId = e.currentTarget.dataset.userId;
        const currentStatus = parseInt(e.currentTarget.dataset.currentStatus);
        const newStatus = currentStatus === 1 ? 0 : 1;
        this.toggleUserStatus(userId, newStatus);
      });
    });

    document.querySelectorAll(".delete-user-btn").forEach((button) => {
      button.addEventListener("click", (e) => {
        const userId = e.currentTarget.dataset.userId;
        this.showConfirmDialog(
          "Supprimer l'utilisateur",
          "Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.",
          async () => {
            try {
              const response = await fetch("./delete_user.php", {
                method: "POST",
                headers: {
                  "Content-Type": "application/x-www-form-urlencoded",
                },
                body: `id_utilisateur=${encodeURIComponent(userId)}`,
              });
              const result = await response.json();

              if (result.success) {
                this.showNotification(
                  "Utilisateur supprimé avec succès.",
                  "success"
                );
                this.fetchUsers(); // Refresh the list
              } else {
                this.showNotification("Erreur: " + result.message, "error");
              }
            } catch (error) {
              console.error("Error deleting user:", error);
              this.showNotification(
                "Erreur de connexion lors de la suppression de l'utilisateur.",
                "error"
              );
            }
          }
        );
      });
    });
    // Edit user button functionality will be added later
  }

  async toggleUserStatus(userId, newStatus) {
    try {
      const response = await fetch("./update_user_status.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `id_utilisateur=${encodeURIComponent(
          userId
        )}&est_actif=${newStatus}`,
      });
      const result = await response.json();

      if (result.success) {
        this.showNotification(result.message, "success");
        this.fetchUsers(); // Refresh the user list
      } else {
        this.showNotification("Erreur: " + result.message, "error");
      }
    } catch (error) {
      console.error("Error toggling user status:", error);
      this.showNotification(
        "Erreur de connexion lors de la mise à jour du statut de l'utilisateur.",
        "error"
      );
    }
  }

  setupAddUserForm() {
    const addUserBtn = document.getElementById("add-new-user-btn");
    const addUserModal = document.getElementById("addUserModal");
    const closeUserModal = addUserModal.querySelector(".close-button");
    const userForm = document.getElementById("addUserForm");

    if (addUserBtn) {
      addUserBtn.addEventListener("click", () => {
        this.openModal("addUserModal");
        userForm.reset(); // Clear form on open
      });
    }

    if (addUserModal && closeUserModal && userForm) {
      closeUserModal.addEventListener("click", () => {
        this.closeModal(addUserModal);
      });

      userForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        console.log("Add User Form Submitted!"); // Debugging line
        if (this.validateForm(userForm)) {
          const formData = new FormData(userForm);
          await this.addAdminUser(formData);
        }
      });
    }
  }

  async addAdminUser(formData) {
    try {
      const response = await fetch("./add_new_user.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        this.showNotification(result.message, "success");
        this.closeModal(document.getElementById("addUserModal")); // Close modal
        this.fetchUsers(); // Refresh the user list
      } else {
        this.showNotification(result.message, "error");
      }
    } catch (error) {
      console.error("Error adding user:", error);
      this.showNotification(
        "Une erreur est survenue lors de l'ajout de l'utilisateur.",
        "error"
      );
    }
  }

  async fetchStudents() {
    console.log("fetchStudents: Function started."); // Debugging: Check if function is called
    const studentsTableBody = document.getElementById("studentsTableBody");
    const loadingIndicator = document.getElementById("loading-students");
    const emptyState = document.getElementById("empty-students-state");

    if (!studentsTableBody) {
      console.log(
        "fetchStudents: Required elements for students table not found."
      );
      return; // Only run if on etudiants.html
    }

    studentsTableBody.innerHTML = ""; // Clear existing students
    loadingIndicator.style.display = "block";
    emptyState.style.display = "none";

    try {
      console.log(
        "fetchStudents: Attempting to fetch data from get_students.php"
      ); // Debugging: Before fetch
      const response = await fetch(
        `./get_students.php?timestamp=${new Date().getTime()}`
      );
      console.log("fetchStudents: Received response.", response); // Debugging: After fetch

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();
      console.log("fetchStudents: Parsed JSON data.", data); // Debugging: After JSON parse

      loadingIndicator.style.display = "none";

      if (data.success && data.students.length > 0) {
        console.log("fetchStudents: Students found, rendering table."); // Debugging: Students found
        data.students.forEach((student) => {
          const statusText = student.est_actif == 1 ? "Actif" : "Archivé";
          const statusClass =
            student.est_actif == 1 ? "badge-success" : "badge-secondary";
          const archiveButtonText =
            student.est_actif == 1 ? "Archiver" : "Désarchiver";
          const archiveButtonClass =
            student.est_actif == 1 ? "btn-warning" : "btn-info";

          const row = `
                        <tr>
                            <td>${student.nom_complet}</td>
                            <td>${student.id_filiere || "N/A"}</td>
                            <td>${student.email}</td>
                            <td><span class="badge ${statusClass}">${statusText}</span></td>
                            <td>${student.moyenne_generale || "N/A"}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline btn-sm edit-student-btn" data-student-id="${
                                      student.id_utilisateur
                                    }"><i class="fas fa-edit"></i></button>
                                    <button class="btn ${archiveButtonClass} btn-sm archive-student-btn" data-student-id="${
            student.id_utilisateur
          }" data-current-status="${
            student.est_actif
          }"><i class="fas fa-archive"></i> ${archiveButtonText}</button>
                                </div>
                            </td>
                        </tr>
                    `;
          studentsTableBody.insertAdjacentHTML("beforeend", row);
        });
        this.setupArchiveStudentButtons(); // Attach event listeners after rendering
      } else {
        console.log(
          "fetchStudents: No students found or data.success is false.",
          data
        ); // Debugging: No students
        emptyState.style.display = "block";
      }
    } catch (error) {
      console.error("fetchStudents: Error fetching students:", error); // Debugging: Catch errors
      loadingIndicator.style.display = "none";
      emptyState.style.display = "block";
      this.showNotification(
        "Erreur lors du chargement des étudiants.",
        "error"
      );
    }
  }

  async archiveStudent(studentId, newStatus) {
    // TEMPORARY: Directly execute the action without confirmation dialog for debugging
    console.log("TEMPORARY BYPASS: Directly attempting archive action.");
    try {
      const response = await fetch("./archive_student.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `id_utilisateur=${encodeURIComponent(
          studentId
        )}&est_actif=${encodeURIComponent(newStatus)}`,
      });
      const result = await response.json();

      if (result.success) {
        this.showNotification(result.message, "success");
        this.fetchStudents(); // Refresh the student list
      } else {
        this.showNotification("Erreur: " + result.message, "error");
      }
    } catch (error) {
      console.error("Error archiving student (bypassed dialog):", error);
      this.showNotification(
        "Erreur de connexion lors de l'archivage de l'étudiant.",
        "error"
      );
    }
  }

  setupArchiveStudentButtons() {
    console.log("Setting up archive student buttons.");
    const archiveButtons = document.querySelectorAll(".archive-student-btn");
    console.log(`Found ${archiveButtons.length} archive buttons.`);
    archiveButtons.forEach((button) => {
      button.addEventListener("click", (e) => {
        console.log("Archive button clicked!");
        const studentId = e.currentTarget.dataset.studentId;
        const currentStatus = parseInt(e.currentTarget.dataset.currentStatus);
        const newStatus = currentStatus === 1 ? 0 : 1; // Toggle status
        this.archiveStudent(studentId, newStatus);
      });
    });
  }

  // New method to fetch and display academic modules
  async fetchModules() {
    console.log("Fetching academic modules...");
    const modulesTableBody = document.querySelector("#modulesTableBody");
    const loadingIndicator = document.getElementById("loading-modules");
    const emptyState = document.getElementById("empty-modules-state");

    if (!modulesTableBody) return; // Only run if on programmes.html

    modulesTableBody.innerHTML = ""; // Clear existing modules
    loadingIndicator.style.display = "block";
    emptyState.style.display = "none";

    try {
      const response = await fetch("./get_modules.php");
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      const data = await response.json();

      console.log("Response from get_modules.php:", data); // Add this line

      loadingIndicator.style.display = "none";

      if (data.success && data.modules.length > 0) {
        console.log("Academic modules fetched successfully:", data.modules);
        data.modules.forEach((module) => {
          const row = `
                        <tr>
                            <td>${module.nom_module}</td>
                            <td>${module.code_module}</td>
                            <td>${module.coefficient}</td>
                            <td>Semestre ${module.semestre}</td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-outline btn-sm edit-module-btn" data-module-id="${module.id_module}"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-danger btn-sm delete-module-btn" data-module-id="${module.id_module}"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    `;
          modulesTableBody.insertAdjacentHTML("beforeend", row);
        });
        this.setupModuleActionButtons(); // Attach event listeners after rendering
      } else {
        emptyState.style.display = "block";
      }
    } catch (error) {
      console.error("Error fetching academic modules:", error);
      loadingIndicator.style.display = "none";
      emptyState.style.display = "block";
      this.showNotification(
        "Erreur lors du chargement des modules académiques.",
        "error"
      );
    }
  }

  // New method to handle adding a new module
  async addModule(formData) {
    try {
      const response = await fetch("./add_module.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        this.showNotification(result.message, "success");
        this.closeModal(document.getElementById("module-form-modal")); // Close modal
        this.fetchModules(); // Refresh the modules list
      } else {
        this.showNotification(result.message, "error");
      }
    } catch (error) {
      console.error("Error adding module:", error);
      this.showNotification(
        "Une erreur est survenue lors de l'ajout du module.",
        "error"
      );
    }
  }

  // New method to handle deleting a module
  async deleteModule(moduleId) {
    this.showConfirmDialog(
      "Supprimer le module",
      "Êtes-vous sûr de vouloir supprimer ce module ? Cette action est irréversible.",
      async () => {
        try {
          const response = await fetch("./delete_module.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `id_module=${encodeURIComponent(moduleId)}`,
          });
          const result = await response.json();

          if (result.success) {
            this.showNotification("Module supprimé avec succès.", "success");
            this.fetchModules(); // Refresh the modules list
          } else {
            this.showNotification("Erreur: " + result.message, "error");
          }
        } catch (error) {
          console.error("Error deleting module:", error);
          this.showNotification(
            "Erreur de connexion lors de la suppression du module.",
            "error"
          );
        }
      }
    );
  }

  // New method to setup action buttons for modules (edit/delete)
  setupModuleActionButtons() {
    document.querySelectorAll(".delete-module-btn").forEach((button) => {
      button.addEventListener("click", (e) => {
        const moduleId = e.currentTarget.dataset.moduleId;
        this.deleteModule(moduleId);
      });
    });

    // Edit button functionality will be added later if needed
    document.querySelectorAll(".edit-module-btn").forEach((button) => {
      button.addEventListener("click", (e) => {
        const moduleId = e.currentTarget.dataset.moduleId;
        console.log("Edit module button clicked for ID:", moduleId);
        // Implement edit logic here (e.g., populate form and open modal)
        this.showNotification(
          "Fonctionnalité d'édition à implémenter.",
          "info"
        );
      });
    });
  }

  // New method to set up the Add Module form modal
  setupAddModuleForm() {
    console.log("setupAddModuleForm called.");
    const addModuleButton = document.getElementById("add-module-btn");
    const moduleFormModal = document.getElementById("module-form-modal");
    const moduleForm = document.getElementById("moduleForm");

    if (addModuleButton) {
      console.log("Add Module Button found.");
      addModuleButton.addEventListener("click", () => {
        console.log("Add Module Button clicked. Opening modal.");
        if (moduleFormModal) {
          this.openModal("module-form-modal");
        } else {
          console.error("Module modal not found!");
        }
      });
    } else {
      console.log("Add Module Button not found.");
    }

    if (moduleFormModal && moduleForm) {
      const closeModuleModal = moduleFormModal.querySelector(".close-button");
      if (closeModuleModal) {
        closeModuleModal.addEventListener("click", () => {
          console.log("Close button clicked.");
          this.closeModal(moduleFormModal);
        });
      }

      moduleForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        console.log("Module form submitted.");
        if (this.validateForm(moduleForm)) {
          const formData = new FormData(moduleForm);
          await this.addModule(formData);
        }
      });
    }
  }

  setupAddStudentForm() {
    console.log("Setting up add student form.");
    const studentRegistrationForm = document.getElementById(
      "student-registration-form"
    );
    const studentFormModal = document.getElementById("student-form-modal");

    if (studentRegistrationForm) {
      studentRegistrationForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        console.log("Student registration form submitted.");
        if (this.validateForm(studentRegistrationForm)) {
          const formData = new FormData(studentRegistrationForm);
          await this.addStudent(formData); // Call new method to handle submission
        }
      });
    }

    if (studentFormModal) {
      // This assumes modal open/close buttons are handled by setupModals
      // But if there's a specific close button within this modal, handle it here
      const closeButton = studentFormModal.querySelector(".close-button");
      if (closeButton) {
        closeButton.addEventListener("click", () => {
          this.closeModal(studentFormModal);
        });
      }
    }
  }

  async addStudent(formData) {
    try {
      const response = await fetch("../handle_student_registration.php", {
        method: "POST",
        body: formData,
      });
      const result = await response.json();

      if (result.success) {
        this.showNotification(result.message, "success");
        this.closeModal(document.getElementById("student-form-modal"));
        this.fetchStudents(); // Refresh student list
        // Clear form fields after successful submission if needed
        document.getElementById("student-registration-form").reset();
      } else {
        this.showNotification(result.message, "error");
      }
    } catch (error) {
      console.error("Error adding student:", error);
      this.showNotification(
        "Une erreur est survenue lors de l'ajout de l'étudiant.",
        "error"
      );
    }
  }
}

new AdminDashboard();
