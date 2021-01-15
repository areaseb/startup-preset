const pathname = window.location.pathname;
const token = $('meta[name="token"]').attr('content');
const baseURL = $('meta[name="baseURL"]').attr('content');
window.tableOptions = {
    aaSorting: [],
    responsive: true,
    autoWidth: false,
    pageLength: 100,
    language: {
        search: '_INPUT_',
        searchPlaceholder: 'Scrivi per filtrare...',
        lengthMenu: '_MENU_',
        info: "_START_ di _END_ su un totale di _TOTAL_",
        zeroRecords: "Non ci sono dati",
        infoEmpty: "Non ci sono dati",
        paginate: {
            first:      "Primo",
            previous:   "Precedente",
            next:       "Successivo",
            last:       "Ultimo"
        },
    }
}

//active link in sidebar

$.each($('#main-nav li.nav-item'), function(key, value){
    let temp = $(value).find('a').attr('href').split('/');
    let root = pathname.split('/')[1];

    let last = temp[temp.length-1];

    if(!Number.isInteger(parseInt(last)))
    {
        if(root != '')
        {
            if(root.includes(last))
            {
                $(value).find('a').addClass('active');
                expandParent(value);
                return false;
            }
        }


        if(pathname.includes(last))
        {
            $(value).find('a').addClass('active');
            expandParent(value);
            return false;
        }

        if(pathname.includes(temp[temp.length-2]))
        {
            if(temp[temp.length-2] != 'stats')
            {
                $(value).find('a').addClass('active');
                expandParent(value);
            }
            return false;
        }
    }
});

function expandParent(el)
{
    if($(el).parent('ul.nav-treeview').length > 0)
    {
        $(el).parent('ul.nav-treeview').css('display', 'block');
        $(el).parent('ul').parent('li.has-treeview ').addClass('menu-open');
    }
    return false;
}

//delete element notication
$('button.delete').on('click', function(e){
    let id = $(this).attr('id');
    let form = $('#form-'+id);
    let url = form.attr('action');
    let row = $('tr#row-'+id);
    console.log(row);
    console.log(url);

    e.preventDefault();

    if($(this).hasClass('noCheck'))
    {
        $.post(url, {_token: token, _method: 'DELETE'}).done(function(data){
            if(data === 'done')
            {
                row.remove();
            }
            else
            {
                err(data);
            }
        });
        return false;
    }


    var notyConfirm = new Noty({
        text: '<h6 class="mb-0">Siete sicuri?</h6><hr class="mt-0 mb-1">',
        timeout: false,
        modal: true,
        layout: 'center',
        closeWith: 'button',
        theme: 'bootstrap-v4',
        buttons: [
            Noty.button('Annulla', 'btn btn-light ml-5', function () {
                notyConfirm.close();
            }),

            Noty.button('Sì, elimina <i class="fa fa-trash"></i>', 'btn btn-danger ml-1', function () {
                    $.post(url, {_token: token, _method: 'DELETE'}).done(function(data){
                        if(data === 'done')
                        {
                            row.remove();
                            console.log('here row remove');
                        }
                        else
                        {
                            err(data);
                        }
                    });
                    notyConfirm.close();
                },
                {id: 'button1', 'data-status': 'ok'}
            )
        ]
    }).show();
});


$('a.delete').on('click', function(e){
    e.preventDefault();
    let id = $(this).attr('data-id');
    let form = $('#form-'+id);
    let url = form.attr('action');
    let row = $('tr#row-'+id);
    console.log(url, form, row);
    e.preventDefault();

    var notyConfirm = new Noty({
        text: '<h6 class="mb-0">Siete sicuri?</h6><hr class="mt-0 mb-1">',
        timeout: false,
        modal: true,
        layout: 'center',
        closeWith: 'button',
        theme: 'bootstrap-v4',
        buttons: [
            Noty.button('Annulla', 'btn btn-light ml-5', function () {
                notyConfirm.close();
            }),

            Noty.button('Sì, elimina <i class="fa fa-trash"></i>', 'btn btn-danger ml-1', function () {
                    $.post(url, {_token: token, _method: 'DELETE'}).done(function(data){
                        if(data === 'done')
                        {
                            row.remove();
                            console.log('here row remove');
                        }
                    });
                    notyConfirm.close();
                },
                {id: 'button1', 'data-status': 'ok'}
            )
        ]
    }).show();
});


//modals
$('a.btn-modal').on('click', function(e){
    e.preventDefault();
    $('#global-modal').modal('show').find('.modal-body').load($(this).attr('href'));
    let title = $(this).text();
    if(title =! '')
    {
        title = $(this).attr('data-title');
    }
    $('#global-modal .modal-header h5').html(title);
    if($(this).attr('data-save'))
    {
        $('#global-modal .modal-footer button.btn-save-modal').html($(this).attr('data-save'));
    }

    $('button.btn-save-modal').on('click', function(){
        console.log('ciaoioo');
        if( validateModal($('#global-modal .modal-content form input')) )
        {
            $('#global-modal .modal-content form').submit();
        }
    });
});

function validateModal(inputs)
{
    let count = 0;
    $.each($(inputs), function(){

        if( typeof $(this).attr('required') != 'undefined' )
        {
            if($(this).val() == '')
            {
                $(this).addClass('is-invalid');
                count++;
            }
        }
    } );
    if(count === 0)
    {
        return true;
    }
    return false;
}

//initialize select2bs4
if ( $( '.select2bs4' ).length ) {
    $('.select2bs4').select2({theme: 'bootstrap4', allowClear: true, width: '100%'})
}



//table sorting
$('.table-php th i').on('click', function(){

    let field = $(this).parent('th').attr('data-field');
    let order = $(this).parent('th').attr('data-order');

    let path = window.location.href;
    let targetPath = '';

    if(path.includes('?sort'))
    {
        targetPath = window.location.href.split('?sort')[0];
        window.location.href = targetPath+'?sort='+field+'|'+order;
    }
    else if(path.includes('&sort'))
    {
        targetPath = window.location.href.split('&sort')[0];
        window.location.href = targetPath+'&sort='+field+'|'+order;
    }
    else if(path.includes('?'))
    {
        targetPath = path;
        window.location.href = targetPath+'&sort='+field+'|'+order;
    }
    else
    {
        targetPath = path;
        window.location.href = targetPath+'?sort='+field+'|'+order;
    }

});

if ( $( '.table-php' ).length ) {
    let arr = window.location.href.split('sort=')
    if (arr.length > 1)
    {
        let sorting = arr[1].split('|');

        let field = sorting[0];
        let order = sorting[1];
        let target = $('.table-php th[data-field='+field+']');
        if(order == 'asc')
        {
            target.attr('data-order', 'desc');
        }
        else
        {
            target.attr('data-order', 'asc');
        }
    }
}

$('td.editable').on('dblclick', function(){
    let cell = $(this);
    let value = cell.text();
    let field = cell.attr('data-field');
    let model = cell.parent('tr').attr('data-model');
    let id = cell.parent('tr').attr('data-id');
    let html = '<div class="input-group"><input type="text" class="form-control" ';
    html += 'name="'+field+'"';
    html += 'value="'+value+'">';
    html += '<div class="input-group-append">';
    html += '<button class="input-group-text saveF btn-success"><i class="fa fa-save"></i></button>';
    html += '<button class="input-group-text closeF btn-danger"><i class="far fa-times-circle"></i></button>';
    html +='</div></div>';
    cell.html(html);

    $('button.saveF').on('click', function(){
        let newValue = $(this).parent('div').siblings('input').val();
        let postUrl = baseURL+'update-field';
        cell.html(newValue);
        data = {
            model: model,
            id: id,
            _token: token,
            field: field,
            value: newValue
        }
        $.post(postUrl, data).done(function( response ) {
            if(response !== 'done')
            {
                alert('not found');
            }
        });
    });

    $('button.closeF').on('click', function(){
        cell.html(value);
    });

});

if( $('.alert.alert-danger').length !== 0 )
{
    setTimeout(function(){
        $('.alert.alert-danger').alert('close');
    }, 5000);
}


if( $('input.is-invalid').length !== 0 )
{
    $('input.is-invalid').on('focusin', function(){
        $(this).removeClass('is-invalid');
    });
}

$('form').on('focusin', 'input.is-invalid', function(){
    $(this).removeClass('is-invalid');
})

if( $('input.input-decimal').length !== 0 )
{

    $.each($('input.input-decimal'), function(){
        let price = $(this).val();
        if(price != '')
        {
            price = parseFloat(price).toFixed(2);
            $(this).val(price);
        }
    });

    $('input.input-decimal').on('focusout', function(){
        let price = $(this).val();
        if(price != '')
        {
            price = price.replace(',', '.');
            price = parseFloat(price).toFixed(2);
            $(this).val(price);
        }
    });
}


let pass = str => new Noty({text: str, type: 'success', theme: 'bootstrap-v4'}).show();
let err = str => new Noty({text: str, type: 'error', theme: 'bootstrap-v4'}).show();
