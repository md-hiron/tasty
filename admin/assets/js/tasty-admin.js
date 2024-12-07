;(function($){
    const siteUrl            = window.location.origin;
    const getAllUserReport    = siteUrl + '/tasty/wp-json/tasty/v1/get_all_user_report';
    const saveChoiceEndPoint = siteUrl + '/tasty/wp-json/tasty/v1/save_choices';

    async function fethcUserReport(){
        try{
            const response = await fetch( getAllUserReport, {
                headers: {
                    'X-WP-Nonce': wpApiSettings.nonce 
                },
                credentials: 'include'
            } );
    
            if( ! response.ok ){
                throw new Error( 'Failed to fetch all user report' );
            }

            return await response.json();

        }catch( error ){
            throw new Error( Error );
        }
    }

    function all_user_report_html( data ){
        if( ! Array.isArray(data) ){
            return '';
        }

        let html = '';

        data.forEach( item => {
            html += `<tr>
                <td>${item?.email}</td>
                <td>${item?.like_share}</td>
                <td>${item?.total_interactions}</td>
                <td>${item?.last_interaction}</td>
            </tr>`
        } );

        return html;
    }

    function append_user_report(){
        const userReportTbody   = document.getElementById('all_user_report');
        const userReportLoading = document.getElementById('all-user-loading');
        console.log(userReportTbody);
        if( userReportTbody ){
            fethcUserReport().then( data => {
                //Inserting user report to the DOM
                userReportTbody.innerHTML = all_user_report_html( data );

                //hide loading after fetching user report
                if( userReportLoading ){
                    userReportLoading.style.display = 'none';
                }
            } )
        }
    }

    append_user_report();
})(jQuery)