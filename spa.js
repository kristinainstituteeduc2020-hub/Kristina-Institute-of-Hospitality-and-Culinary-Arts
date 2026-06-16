const $ = (sel) => document.querySelector(sel);

function escapeHtml(s){
  return (s ?? '').toString()
    .replaceAll('&','&amp;')
    .replaceAll('<','<')
    .replaceAll('>','>')
    .replaceAll('"','"')
    .replaceAll("'",'&#039;');
}

function kindLabel(type){
  if(type === 'tesda_assessment') return 'TESDA Assessment';
  if(type === 'tesda_training') return 'Community-based Training';
  if(type === 'news') return 'News';
  if(type === 'updates') return 'Update';
  if(type === 'advertisement') return 'Advertisement';
  return 'Details';
}

function renderDetails(item){
  // If you want: remove/show qualifications list from content here.

  const title = escapeHtml(item.title);
  const createdAt = item.created_at ? new Date(item.created_at).toISOString().slice(0,10) : '';
  const content = item.content ? escapeHtml(item.content) : '';

  const img = item.image_path || '';
  const video = item.video_path || '';
  const att = item.attachment_path || '';

  $('#spa-subtitle').textContent = kindLabel(item.type);
  const back = $('#backLink');
  if(item.type === 'tesda_assessment' || item.type === 'tesda_training') back.setAttribute('href','../tesda.html');
  else back.setAttribute('href','../index.html');

  return `
    <div id="detailsInner">
      ${img ? `
        <div class="details-media">
          <img src="${img}" alt="${title}" />
        </div>
      ` : ''}

      <div class="details-body">
        <h1 class="details-title">${title}</h1>
        <div class="details-meta">
          <span>${escapeHtml(item.type)}</span>
          ${item.author_name ? `<span>By ${escapeHtml(item.author_name)}</span>` : ''}
          ${createdAt ? `<span>${escapeHtml(createdAt)}</span>` : ''}
        </div>

        ${content ? `<div class="details-content">${content}</div>` : ''}

        <!-- Qualification list removed from TESDA detail cards -->

        <div class="details-grid">
          <div>
            ${video ? `
              <div class="section-block">
                <h3 style="margin:0 0 10px; font-size:16px;">Video</h3>
                <video controls playsinline style="width:100%; max-height:320px; object-fit:contain; display:block;">
                  <source src="${video}" />
                </video>
              </div>
            ` : ''}

            ${att ? `
              <div class="section-block">
                <h3 style="margin:18px 0 10px; font-size:16px;">Attachment (PDF/Files)</h3>
                <a class="btn-link" href="${att}" target="_blank" rel="noreferrer">Download Attachment</a>
              </div>
            ` : ''}
          </div>

          <div>
            <div class="section-block">
              <h3 style="margin:0 0 10px; font-size:16px;">Uploaded Image</h3>
              ${img ? `<a class="btn-link btn-out" href="${img}" target="_blank" rel="noreferrer">View Image</a>` : `<div class="muted">No image uploaded.</div>`}
            </div>
          </div>
        </div>

        <div class="details-actions">
          <a class="btn-link btn-out" href="${item.type === 'tesda_assessment' || item.type === 'tesda_training' ? '../tesda.html' : '../index.html'}">Back</a>
        </div>
      </div>
    </div>
  `;
}

async function init(){
  const params = new URLSearchParams(location.search);
  const type = params.get('type') || 'tesda_assessment';
  const id = parseInt(params.get('id') || '0', 10);

  const card = $('#detailsCard');
  card.innerHTML = `<div class="muted">Loading details…</div>`;

  if(!id){
    card.innerHTML = `<div class="alert alert-danger" role="alert">Missing id.</div>`;
    return;
  }

  try{
    const res = await fetch(`../api/post_detail.php?type=${encodeURIComponent(type)}&id=${encodeURIComponent(id)}`);
    if(!res.ok) throw new Error(await res.text().catch(()=>''));
    const data = await res.json();

    if(!data.ok) throw new Error(data.error || 'Failed');

    card.innerHTML = renderDetails(data.item);
  }catch(e){
    console.error(e);
    card.innerHTML = `<div class="alert alert-danger" role="alert">Failed to load details.</div>`;
  }
}

if(document.readyState === 'loading'){
  document.addEventListener('DOMContentLoaded', init);
}else{
  init();
}

