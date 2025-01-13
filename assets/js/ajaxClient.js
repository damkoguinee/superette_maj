$(document).ready(function(){
    $('#search_client').keyup(function(){
        $('#result-search').html("");

        var utilisateur = $(this).val();
        var currentUrl = window.location.pathname; // Récupérer l'URL actuelle
        if (utilisateur != '') {
            $.ajax({
                type: 'GET',
                url: currentUrl, // Utiliser l'URL actuelle
                data: { search: utilisateur },
                success: function(data){
                    if (data.length > 0) {
                        // Update the result container with the search results
                        for (var i = 0; i < data.length; i++) {
                            var clickableName = '<a href="' + currentUrl + '?id_client_search=' + encodeURIComponent(data[i].id) + '">' + data[i].nom + '</a>';

                            $('#result-search').append('<div style="text-decoration: underline; ">' + clickableName + '</div>');
                        }
                    } else {
                        $('#result-search').html("<div style='font-size: 20px; text-align: center; margin-top: 10px'>Aucun collaborateur trouvé</div>");
                    }
                }
            });
        }
    });

    $('#search_client_vente').keyup(function(){
        $('#result-search_vente').html("");

        var utilisateur = $(this).val();
        var currentUrl = window.location.pathname; // Récupérer l'URL actuelle
        if (utilisateur != '') {
            $.ajax({
                type: 'GET',
                url: currentUrl, // Utiliser l'URL actuelle
                data: { search_client: utilisateur },
                success: function(data){
                    if (data.length > 0) {
                        // Update the result container with the search results
                        for (var i = 0; i < data.length; i++) {
                            var clickableName = '<a href="' + currentUrl + '?id_client_search=' + encodeURIComponent(data[i].id) + '">' + data[i].nom + '</a>';

                            $('#result-search_vente').append('<div style="text-decoration: underline; ">' + clickableName + '</div>');
                        }
                    } else {
                        $('#result-search_vente').html("<div style='font-size: 20px; text-align: center; margin-top: 10px'>Aucun collaborateur trouvé</div>");
                    }
                }
            });
        }
    });

    $('#search_all_user').keyup(function(){
        $('#result_search_all_user').html("");

        var utilisateur = $(this).val();
        var currentUrl = window.location.pathname; // Récupérer l'URL actuelle
        if (utilisateur != '') {
            $.ajax({
                type: 'GET',
                url: currentUrl, // Utiliser l'URL actuelle
                data: { search_all_user : utilisateur },
                success: function(data){
                    if (data.length > 0) {
                        // Update the result container with the search results
                        for (var i = 0; i < data.length; i++) {
                            var clickableName = '<a href="' + currentUrl + '?id_user=' + encodeURIComponent(data[i].id) + '">' + data[i].nom + '</a>';

                            $('#result_search_all_user').append('<div style="text-decoration: underline; ">' + clickableName + '</div>');
                        }
                    } else {
                        $('#result_search_all_user').html("<div style='font-size: 20px; text-align: center; margin-top: 10px'>Aucun collaborateur trouvé</div>");
                    }
                }
            });
        }
    });

    $('#search_personnel').keyup(function(){
        $('#result_search_personnel').html("");

        var utilisateur = $(this).val();
        var currentUrl = window.location.pathname; // Récupérer l'URL actuelle
        if (utilisateur != '') {
            $.ajax({
                type: 'GET',
                url: currentUrl, // Utiliser l'URL actuelle
                data: { search_personnel : utilisateur },
                success: function(data){
                    if (data.length > 0) {
                        // Update the result container with the search results
                        for (var i = 0; i < data.length; i++) {
                            var clickableName = '<a href="' + currentUrl + '?id_personnel=' + encodeURIComponent(data[i].id) + '">' + data[i].nom + '</a>';

                            $('#result_search_personnel').append('<div style="text-decoration: underline; ">' + clickableName + '</div>');
                        }
                    } else {
                        $('#result_search_personnel').html("<div style='font-size: 20px; text-align: center; margin-top: 10px'>Aucun collaborateur trouvé</div>");
                    }
                }
            });
        }
    });
});