// Nova Panel Client Library
window.Nova = {
  // Mobile drawer toggle
  initMobileNav() {
    const toggle = document.querySelector('.mobile-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (toggle && sidebar) {
      toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
      });
      // Close when clicking outside on mobile
      document.addEventListener('click', (e) => {
        if (!sidebar.contains(e.target) && !toggle.contains(e.target) && sidebar.classList.contains('open')) {
          sidebar.classList.remove('open');
        }
      });
    }
  },

  // Toast Notification
  toast(message, type = 'info') {
    let container = document.getElementById('nova-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.id = 'nova-toast-container';
      container.style.cssText = 'position:fixed;bottom:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const colors = {
      success: '#10b981',
      error: '#ef4444',
      warning: '#f59e0b',
      info: '#38bdf8'
    };
    toast.style.cssText = `background:#141b2d;border-left:4px solid ${colors[type] || colors.info};color:#f1f5f9;padding:12px 18px;border-radius:8px;box-shadow:0 10px 25px rgba(0,0,0,0.5);font-size:0.875rem;display:flex;align-items:center;gap:10px;animation:slideIn 0.25s ease;`;
    toast.innerHTML = `<span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transition = 'opacity 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, 4000);
  },

  // Setup Live Preview for Image Uploads and URLs (Branding settings)
  initLivePreview(fileInputId, urlInputId, previewImgId) {
    const fileInput = document.getElementById(fileInputId);
    const urlInput = document.getElementById(urlInputId);
    const previewImg = document.getElementById(previewImgId);

    if (!previewImg) return;

    if (fileInput) {
      fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = (re) => {
            previewImg.src = re.target.result;
          };
          reader.readAsDataURL(file);
        }
      });
    }

    if (urlInput) {
      urlInput.addEventListener('input', (e) => {
        const val = e.target.value.trim();
        if (val) {
          previewImg.src = val;
        }
      });
    }
  },

  // Global Arix Copy Helper with Authentic Sound
  copy(text) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
      const panelSoundsEnabled = localStorage.getItem('panelSounds') !== 'false';
      if (panelSoundsEnabled) {
        try {
          const copySound = new Audio('/arix/copy.mp3');
          copySound.volume = 0.25;
          copySound.play().catch(() => {});
        } catch (e) {}
      }
      this.toast('Copied to clipboard: ' + text, 'success');
    }).catch((err) => {
      console.error('Clipboard copy error:', err);
    });
  },

  // Server Cards Power Actions & Status Synchronization
  initServerCards() {
    const cards = document.querySelectorAll('.server-card-box, .server-row-box');
    if (!cards.length) return;

    cards.forEach((card) => {
      const serverId = card.getAttribute('data-server-id');
      const actionBtns = card.querySelectorAll('.btn-srv-action');
      const badgeWrap = card.querySelector('.server-card-badge-wrap');

      const setBadge = (status) => {
        const s = (status || 'offline').toLowerCase();
        card.setAttribute('data-status', s);

        if (badgeWrap) {
          let cls = 'badge-offline';
          let label = 'Offline';
          if (s === 'online' || s === 'running') {
            cls = 'badge-online';
            label = 'Online';
          } else if (s === 'starting' || s === 'installing') {
            cls = 'badge-starting';
            label = 'Starting...';
          } else if (s === 'stopping') {
            cls = 'badge-stopping';
            label = 'Stopping...';
          }
          badgeWrap.innerHTML = `<span class="status-badge ${cls}"><span class="badge-dot"></span>${label}</span>`;
        }

        const uptimeDot = card.querySelector('.stat-uptime-dot');
        const uptimeStatus = card.querySelector('.stat-uptime-status');
        if (uptimeDot && uptimeStatus) {
          if (s === 'online' || s === 'running') {
            uptimeDot.classList.add('is-live');
            uptimeStatus.textContent = 'Active';
          } else {
            uptimeDot.classList.remove('is-live');
            uptimeStatus.textContent = 'Offline';
          }
        }
      };

      actionBtns.forEach((btn) => {
        btn.addEventListener('click', async (e) => {
          e.preventDefault();
          e.stopPropagation();

          const action = btn.getAttribute('data-action');
          if (!action || !serverId) return;

          // Disable buttons on this card while request is in flight
          actionBtns.forEach((b) => (b.disabled = true));
          btn.classList.add('is-loading');

          // Immediate optimistic feedback
          if (action === 'start') setBadge('starting');
          else if (action === 'restart') setBadge('starting');
          else if (action === 'stop') setBadge('stopping');
          else if (action === 'kill') setBadge('offline');

          try {
            const res = await fetch(`/server/${serverId}/power`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
              },
              body: JSON.stringify({ action })
            });

            const data = await res.json();
            if (res.ok && data.success !== false) {
              window.Nova.toast(`Server power action '${action.toUpperCase()}' dispatched.`, 'success');
              if (action === 'start' || action === 'restart') {
                setTimeout(() => setBadge('online'), 2500);
              } else if (action === 'stop' || action === 'kill') {
                setTimeout(() => setBadge('offline'), 1500);
              }
            } else {
              window.Nova.toast(data.error || 'Failed to dispatch power action.', 'error');
              setBadge(card.getAttribute('data-status') || 'offline');
            }
          } catch (err) {
            console.error('Server power action error:', err);
            window.Nova.toast('Network error sending power action.', 'error');
            setBadge(card.getAttribute('data-status') || 'offline');
          } finally {
            actionBtns.forEach((b) => (b.disabled = false));
            btn.classList.remove('is-loading');
          }
        });
      });
    });

    // Start background stats polling for active running servers
    if (!this._statsPollTimer) {
      this._statsPollTimer = setInterval(() => this.pollServerStats(), 5000);
    }
  },

  async pollServerStats() {
    const liveCards = document.querySelectorAll('.server-card-box[data-status="online"], .server-card-box[data-status="running"], .server-row-box[data-status="online"], .server-row-box[data-status="running"]');
    if (!liveCards.length) return;

    for (const card of liveCards) {
      const serverId = card.getAttribute('data-server-id');
      if (!serverId) continue;

      try {
        const res = await fetch(`/server/${serverId}/stats`, { headers: { Accept: 'application/json' } });
        if (!res.ok) continue;
        const json = await res.json();
        if (json.success && json.data) {
          const stats = json.data;

          // CPU
          const cpuVal = card.querySelector('.stat-cpu-val');
          const cpuBar = card.querySelector('.stat-cpu-bar');
          if (cpuVal && stats.cpu !== undefined) {
            cpuVal.textContent = Number(stats.cpu).toFixed(2) + '%';
          }
          if (cpuBar && stats.cpu !== undefined) {
            cpuBar.style.width = Math.min(100, Math.max(0, stats.cpu)) + '%';
          }

          // RAM
          const memVal = card.querySelector('.stat-mem-val');
          const memBar = card.querySelector('.stat-mem-bar');
          if (memVal && stats.memory !== undefined) {
            const mib = (stats.memory / (1024 * 1024)).toFixed(1);
            memVal.textContent = mib + ' MiB';
          }
          if (memBar && stats.memoryLimit > 0) {
            const pct = Math.min(100, Math.max(0, (stats.memory / stats.memoryLimit) * 100));
            memBar.style.width = pct + '%';
          }

          // Uptime
          const uptimeVal = card.querySelector('.stat-uptime-val');
          if (uptimeVal && stats.uptime !== undefined) {
            const u = Math.floor(stats.uptime || 0);
            if (u < 60) uptimeVal.textContent = u + 's';
            else if (u < 3600) uptimeVal.textContent = Math.floor(u / 60) + 'm ' + (u % 60) + 's';
            else uptimeVal.textContent = (u / 3600).toFixed(1) + 'h';
          }
        }
      } catch (_) {}
    }
  },

  // Modal Dialog System
  openModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = 'flex';
    el.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    const input = el.querySelector('input:not([type=hidden]):not([disabled]), textarea');
    if (input) setTimeout(() => input.focus(), 80);
  },

  closeModal(id) {
    const el = typeof id === 'string' ? document.getElementById(id) : id;
    if (!el) return;
    el.classList.remove('is-open');
    el.style.display = 'none';
    const anyOpen = document.querySelector('.nova-modal-overlay.is-open, .nova-modal-backdrop.is-open');
    if (!anyOpen) {
      document.body.style.overflow = '';
    }
  },

  initModals() {
    // Backdrop click and close buttons
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('nova-modal-overlay') || e.target.classList.contains('nova-modal-backdrop')) {
        window.Nova.closeModal(e.target);
      }
      if (e.target.matches('[data-modal-close]') || e.target.closest('[data-modal-close]')) {
        const modal = e.target.closest('.nova-modal-overlay, .nova-modal-backdrop, [id^="modal-"]');
        if (modal) {
          window.Nova.closeModal(modal);
        }
      }
    });

    // ESC key closes topmost modal
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('.nova-modal-overlay.is-open, .nova-modal-backdrop.is-open, [id^="modal-"][style*="display: flex"], [id^="modal-"][style*="display:flex"]');
        if (openModals.length > 0) {
          window.Nova.closeModal(openModals[openModals.length - 1]);
        }
      }
    });
  },

  // Header Bar Power Actions on Sub-Pages
  initSubpagePower() {
    const powerBtns = document.querySelectorAll('.server-subpage-header [data-subpage-power]');
    if (!powerBtns.length) return;

    powerBtns.forEach((btn) => {
      btn.addEventListener('click', async (e) => {
        e.preventDefault();
        const action = btn.getAttribute('data-subpage-power');
        const serverId = btn.getAttribute('data-server-id');
        if (!action || !serverId) return;

        powerBtns.forEach((b) => (b.disabled = true));
        btn.classList.add('is-loading');

        try {
          const res = await fetch(`/server/${serverId}/power`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            body: JSON.stringify({ action })
          });
          const data = await res.json();
          if (res.ok && data.success !== false) {
            window.Nova.toast(`Server power action '${action.toUpperCase()}' dispatched.`, 'success');
            const statusEl = document.querySelector('.server-subpage-header .server-status-pill');
            if (statusEl) {
              if (action === 'start' || action === 'restart') {
                statusEl.innerHTML = '<span class="status-badge badge-starting"><span class="badge-dot"></span>Starting...</span>';
                setTimeout(() => {
                  statusEl.innerHTML = '<span class="status-badge badge-online"><span class="badge-dot"></span>Online</span>';
                }, 2500);
              } else if (action === 'stop' || action === 'kill') {
                statusEl.innerHTML = '<span class="status-badge badge-offline"><span class="badge-dot"></span>Offline</span>';
              }
            }
          } else {
            window.Nova.toast(data.error || 'Failed to dispatch power action.', 'error');
          }
        } catch (err) {
          window.Nova.toast('Network error sending power action.', 'error');
        } finally {
          powerBtns.forEach((b) => (b.disabled = false));
          btn.classList.remove('is-loading');
        }
      });
    });
  },

  // Server View Switching (Card / List) & Search Filtering
  initServerView() {
    let saved = 'card';
    try {
      saved = localStorage.getItem('nova_server_view') || 'card';
    } catch (_) {}
    this.switchServerView(saved);
  },

  switchServerView(mode) {
    const targetMode = mode === 'list' ? 'list' : 'card';
    try {
      localStorage.setItem('nova_server_view', targetMode);
    } catch (_) {}

    const cardGrids = document.querySelectorAll('.server-cards-grid');
    const listViews = document.querySelectorAll('.server-list-view');
    const cardBtns = document.querySelectorAll('.btn-view-card');
    const listBtns = document.querySelectorAll('.btn-view-list');

    if (targetMode === 'list') {
      cardGrids.forEach((el) => (el.style.display = 'none'));
      listViews.forEach((el) => (el.style.display = 'flex'));
      cardBtns.forEach((btn) => btn.classList.remove('active'));
      listBtns.forEach((btn) => btn.classList.add('active'));
    } else {
      cardGrids.forEach((el) => (el.style.display = 'flex'));
      listViews.forEach((el) => (el.style.display = 'none'));
      cardBtns.forEach((btn) => btn.classList.add('active'));
      listBtns.forEach((btn) => btn.classList.remove('active'));
    }
  },

  filterServers(query) {
    const q = (query || '').toLowerCase().trim();
    const cards = document.querySelectorAll('.server-card-box');
    const rows = document.querySelectorAll('.server-row-box');
    let matchCount = 0;

    cards.forEach((card) => {
      const text = (card.getAttribute('data-search') || card.textContent || '').toLowerCase();
      const match = !q || text.includes(q);
      card.style.display = match ? '' : 'none';
      if (match) matchCount++;
    });

    rows.forEach((row) => {
      const text = (row.getAttribute('data-search') || row.textContent || '').toLowerCase();
      const match = !q || text.includes(q);
      row.style.display = match ? '' : 'none';
    });

    const noResults = document.getElementById('serversNoResults');
    if (noResults) {
      noResults.style.display = (matchCount === 0 && q) ? 'block' : 'none';
    }
  }
};

window.arixCopy = function (text) {
  window.Nova.copy(text);
};

window.switchServerView = function (mode) {
  if (window.Nova && window.Nova.switchServerView) {
    window.Nova.switchServerView(mode);
  }
};

window.filterServers = function (query) {
  if (window.Nova && window.Nova.filterServers) {
    window.Nova.filterServers(query);
  }
};

document.addEventListener('DOMContentLoaded', () => {
  window.Nova.initMobileNav();
  window.Nova.initServerCards();
  window.Nova.initModals();
  window.Nova.initSubpagePower();
  window.Nova.initServerView();

  // Auto-init branding previews if on settings page
  window.Nova.initLivePreview('logo_file_input', 'logo_url_input', 'logo_preview_img');
  window.Nova.initLivePreview('favicon_file_input', 'favicon_url_input', 'favicon_preview_img');
});


