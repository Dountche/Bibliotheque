// ajax_account.js
$(function(){
    function bindAjaxForm(formSelector){
      $(formSelector).on('submit', function(e){
        e.preventDefault();
        const $form = $(this),
              url   = $form.attr('action'),
              data  = new FormData(this);
  
        $.ajax({
          url: url,
          method: 'POST',
          data: data,
          processData: false,
          contentType: false,
          dataType: 'json'
        })
        .done(resp => {
          if (resp.status === 'success') {
            Swal.fire('Succès', resp.message, 'success')
              .then(() => {
                // fermer le modal
                $('#accountModal').modal('hide');
                // rafraîchir la page courante
                location.reload();
              });
          } else {
            Swal.fire('Erreur', resp.message, 'error');
          }
        })
        .fail(() => {
          Swal.fire('Erreur', 'Impossible de joindre le serveur', 'error');
        });
      });
    }
  
    bindAjaxForm('#profileForm');
    bindAjaxForm('#photoForm');
    bindAjaxForm('#passwordForm');
  });
  