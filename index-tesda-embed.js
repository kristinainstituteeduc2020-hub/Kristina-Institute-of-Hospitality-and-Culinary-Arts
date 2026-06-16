(function(){
  function escapeHtml(s){
    return (s ?? '').toString()
      .replaceAll('&','&amp;')
      .replaceAll('<','<')
      .replaceAll('>','>')
      .replaceAll('"','"')
      .replaceAll("'",'&#039;');
  }

  async function fetchJSON(url){
    const res = await fetch(url);
    if(!res.ok) throw new Error(await res.text());
    return res.json();
  }

  function escapeAttr(s){
    return (s ?? '').toString().replaceAll('"','"');
  }

  function fmtDate(d){
    if(!d) return '';
    try { return new Date(d).toLocaleDateString(); } catch(e){ return ''; }
  }

  const TESDA_CATEGORIES = [
    { key:'UTPRAS', title:'UTPRAS', short:'Programs related to UTPRAS and assessment preparation.' },
    { key:'Assessment Certification & Accreditation', title:'Assessment Certification & Accreditation', short:'Admin-posted assessment and certification programs.' },
    { key:'Community-Based Training Qualifications', title:'Community-Based Training Qualifications', short:'Community training programs posted by the admin.' },
  ];

  function renderCategoryCard(cat){
    const key = escapeHtml(cat.key);
    return `
      <article class="card" style="display:flex; flex-direction:column; min-height:230px;">
        <div class="card-body">
          <div class="badges"><span class="badge badge-primary">TESDA</span></div>
          <h3 class="card-title">${escapeHtml(cat.title)}</h3>
          <div class="card-content">${escapeHtml(cat.short)}</div>
          <div class="card-actions" style="margin-top:auto;">
            <button type="button" class="small-link" style="cursor:pointer; border:0; background:transparent; padding:0; color:inherit;" onclick="window.__openTesdaEmbedModal('${key}')">View</button>
          </div>
        </div>
      </article>
    `;
  }

  function ensureModal(){
    if(document.getElementById('tesdaEmbedModalOverlay')) return;

    const overlay = document.createElement('div');
    overlay.id = 'tesdaEmbedModalOverlay';
    overlay.style.display = 'none';
    overlay.style.position = 'fixed';
    overlay.style.inset = '0';
    overlay.style.background = 'rgba(0,0,0,.62)';
    overlay.style.zIndex = '9999';
    overlay.style.padding = '18px';
    overlay.innerHTML = `
      <div id="tesdaEmbedModal" style="max-width:980px; margin:0 auto; background:rgba(15,23,42,.98); border:1px solid var(--border); border-radius:18px; overflow:hidden; box-shadow:var(--shadow);">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; padding:14px 16px; border-bottom:1px solid var(--border);">
          <div>
            <div style="font-weight:900; font-size:16px;">TESDA Programs</div>
            <div id="tesdaEmbedModalSub" style="color:var(--muted); font-size:12.5px; margin-top:4px;">—</div>
          </div>
          <button type="button" id="tesdaEmbedModalCloseBtn" class="btn-secondary" style="padding:9px 12px;">Close</button>
        </div>
        <div style="padding:14px 16px;">
          <div id="tesdaEmbedModalBody" class="muted" style="min-height:90px;">Loading…</div>
        </div>
      </div>
    `;
    document.body.appendChild(overlay);

    const closeBtn = document.getElementById('tesdaEmbedModalCloseBtn');
    closeBtn.addEventListener('click', () => { overlay.style.display = 'none'; });
    overlay.addEventListener('click', (e) => { if(e.target === overlay) overlay.style.display = 'none'; });
    document.addEventListener('keydown', (e) => { if(e.key === 'Escape') overlay.style.display = 'none'; });
  }

  async function openModal(catKey){
    ensureModal();
    const overlay = document.getElementById('tesdaEmbedModalOverlay');
    const sub = document.getElementById('tesdaEmbedModalSub');
    const body = document.getElementById('tesdaEmbedModalBody');

    const cat = TESDA_CATEGORIES.find(c => c.key === catKey);
    sub.textContent = cat ? cat.title : catKey;

    overlay.style.display = 'block';
    body.innerHTML = 'Loading programs…';

    try{
      const data = await fetchJSON(`api/tesda_list_by_category.php?category=${encodeURIComponent(catKey)}&limit=50`);
      const items = (data.items || []);

      if(!items.length){
        body.innerHTML = '<div class="muted">No programs posted yet for this category.</div>';
        return;
      }

      body.innerHTML = `
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
          ${items.map(it => {
            const title = escapeHtml(it.title);
            const desc = (it.content || '').trim();
            const preview = escapeHtml(desc.length > 140 ? desc.slice(0,140) + '…' : desc);
            const img = it.image_path ? it.image_path : '';
            const date = fmtDate(it.created_at);

            const detailsUrl =
              (it.type === 'tesda_assessment') ? `view_assessment.php?id=${encodeURIComponent(it.id)}` :
              (it.type === 'tesda_training') ? `view_community_training.php?id=${encodeURIComponent(it.id)}` :
              `view_utpras.php?id=${encodeURIComponent(it.id)}`;

            return `
              <div style="background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:16px; overflow:hidden;">
                <div style="height:130px; background:#0d1528; display:flex; align-items:center; justify-content:center; overflow:hidden;">
                  ${img ? `<img src="${escapeAttr(img)}" alt="${title}" style="width:100%; height:100%; object-fit:cover;" loading="lazy"/>` : `<div style="padding:12px; color:rgba(255,255,255,.8); font-weight:900; text-align:center;">No banner</div>`}
                </div>
                <div style="padding:12px 12px 14px;">
                  <div style="color:var(--muted); font-size:12.5px; display:flex; gap:10px; flex-wrap:wrap;">
                    ${date ? `<span>${escapeHtml(date)}</span>` : ''}
                  </div>
                  <div style="font-weight:950; margin-top:8px; line-height:1.25;">${title}</div>
                  <div style="color:var(--text); margin-top:8px; line-height:1.6; font-size:14px; display:-webkit-box; -webkit-line-clamp:4; -webkit-box-orient:vertical; overflow:hidden;">${preview || '—'}</div>
                  <div style="margin-top:12px; display:flex; justify-content:flex-end;">
                    <a class="small-link" href="${detailsUrl}" style="text-decoration:none;">Learn More</a>
                  </div>
                </div>
              </div>
            `;
          }).join('')}
        </div>
      `;
    }catch(err){
      console.error(err);
      body.innerHTML = '<div class="muted" style="color:var(--danger);">Failed to load programs.</div>';
    }
  }

  window.__openTesdaEmbedModal = (catKey) => openModal(catKey);

  async function init(){
    const host = document.getElementById('tesdaEmbed');
    if(!host) return;

    host.innerHTML = `<div class="grid" style="display:grid; grid-template-columns:repeat(3, 1fr); gap:14px;" >` +
      TESDA_CATEGORIES.map(renderCategoryCard).join('') +
      `</div>`;

    // Basic responsive: rely on existing css grid classes; keep inline container minimal.
    // If your global CSS already handles .grid/cards, you can remove inline styles later.
  }

  init();
})();

