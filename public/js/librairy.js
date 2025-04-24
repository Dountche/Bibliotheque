// public/js/library.js
$(function(){

    // --- 1) Pagination pour les cartes de #docsGrid ---
    function setupGridPagination(perPage) {
      let $items, pages, current;
      function recalc() {
        $items = $('#docsGrid .doc-card:visible');
        pages  = Math.max(Math.ceil($items.length / perPage), 1);
        current = 1;
      }
      function show(p) {
        recalc();
        const start = (p-1)*perPage, end = start + perPage;
        $items.hide().slice(start,end).show();
        current = p;
        render();
      }
      function render() {
        const $pg = $('#pagination-container').empty();
        if (pages <= 1) return;
        if (current>1)
          $pg.append(`<li class="page-item"><a class="page-link" href="#" data-page="${current-1}">&laquo;</a></li>`);
        for(let i=1;i<=pages;i++)
          $pg.append(`<li class="page-item${i===current?' active':''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                      </li>`);
        if (current<pages)
          $pg.append(`<li class="page-item"><a class="page-link" href="#" data-page="${current+1}">&raquo;</a></li>`);
      }
      $('#pagination-container').off('click.pg').on('click.pg','a.page-link', e=>{
        e.preventDefault();
        const p = parseInt($(e.currentTarget).data('page'),10);
        if (p && p!==current) show(p);
      });
      return { refresh: ()=>show(1) };
    }
    window.pager = setupGridPagination(6);
    pager.refresh();
  
    // --- 2) Ouvre PDF ou Office dans un nouvel onglet ---
    window.openViewer = function(fileName){
      const ext = fileName.split('.').pop().toLowerCase();
      if (ext==='pdf') {
        window.open(`view_document.php?file=${fileName}`,'_blank');
      } else {
        const url = encodeURIComponent(location.origin + '/documents/' + fileName);
        window.open(`https://docs.google.com/viewer?url=${url}&embedded=true`,'_blank');
      }
    };
  
    // --- 3) Cacher/Montrer le champ “Autre…” ---
    $('#filterCat').on('change', function(){
      if (this.value==='other') {
        $('#otherCatContainer').show().attr('required',true);
      } else {
        $('#otherCatContainer').hide().removeAttr('required').val('');
      }
      refreshLibrary();
    });
  
    // --- 4) Rafraîchir la librairie via AJAX ---
    function refreshLibrary(){
      const search   = $('#searchInput').val().trim(),
            category = $('#filterCat').val(),
            other    = $('#otherCat').val() ? $('#otherCat').val().trim() : '';
      $.getJSON('ajax_document.php',{ search, category, other })
       .done(resp=>{
         $('#totalCount').text(resp.total);
         let html = '';
         resp.docs.forEach(d=>{
           const esc = s=>$('<div>').text(s).html();
           const isPdf = d.typeMime.includes('pdf');
           const isOff = ['doc','docx','ppt','pptx'].some(e=>d.fichier.toLowerCase().endsWith(e));
           const preview = isPdf
             ? `<div class="mb-2" style="height:200px;overflow:hidden">
                  <iframe src="documents/${encodeURIComponent(d.fichier)}"
                          width="100%" height="100%" style="border:none"></iframe>
                </div>`
             : '';
           let lire;
           if (isPdf||isOff) {
             lire = `<button onclick="openViewer('${esc(d.fichier)}')"
                             class="btn btn-outline-primary btn-sm w-100 mb-2">
                       Lire en ligne
                     </button>`;
           } else {
             lire = `<a href="documents/${esc(d.fichier)}" target="_blank"
                        class="btn btn-outline-secondary btn-sm w-100 mb-2">
                        Voir le fichier
                     </a>`;
           }
           const dl = d.telechargable
             ? `<a href="documents/${esc(d.fichier)}" download
                   class="btn btn-success btn-sm w-100">Télécharger</a>`
             : `<button class="btn btn-secondary btn-sm w-100" disabled>
                  Téléchargement interdit
                </button>`;
           html += `
             <div class="col doc-card"
                  data-categorie="${esc(d.categorie)}"
                  data-titre="${esc(d.titre).toLowerCase()}">
               <div class="card h-100 shadow-sm">
                 <div class="card-body d-flex flex-column">
                   <h5 class="card-title">${esc(d.titre)}</h5>
                   ${preview}
                   ${d.description?`<p class="card-text">${esc(d.description)}</p>`:``}
                   <small class="text-muted mb-3">
                     Publié le ${new Date(d.dateUpload).toLocaleDateString('fr-FR')}
                   </small>
                   <div class="mt-auto">${lire}${dl}</div>
                 </div>
                 <?php if($_SESSION['user']['roles']==='Admin'): ?>
                 <div class="card-footer text-end">
                   <a href="edit_document.php?idDoc=${d.idDoc}" class="btn btn-sm btn-warning">
                     <i class="fa-solid fa-pen-to-square"></i>
                   </a>
                   <a href="del_document.php?idDoc=${d.idDoc}" class="btn btn-sm btn-danger"
                      onclick="return confirm('Confirmer ?')">
                     <i class="fa-solid fa-trash"></i>
                   </a>
                 </div>
                 <?php endif; ?>
               </div>
             </div>`;
         });
         $('#docsGrid').html(html);
         pager.refresh();
       })
       .fail(()=>Swal.fire('Erreur','Impossible de joindre le serveur','error'));
    }
  
    // --- 5) Brancher recherche + pagination initiale ---
    $('#searchInput,#otherCat').on('input',refreshLibrary);
    $('#filterCat').on('change',refreshLibrary);
    refreshLibrary();
  });
  