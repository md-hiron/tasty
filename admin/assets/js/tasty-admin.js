;(function($){
    const siteUrl              = window.location.origin;
    const getAllUserReport     = siteUrl + '/tasty/wp-json/tasty/v1/get_all_user_report';
    const saveChoiceEndPoint   = siteUrl + '/tasty/wp-json/tasty/v1/get_choices';
    const tag_perform_EndPoint = siteUrl + '/tasty/wp-json/tasty/v1/get_tag_performance';

    async function fethcAdminApi( apiEndpoint ){
        try{
            const response = await fetch( apiEndpoint, {
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
       
        if( userReportTbody ){
            fethcAdminApi(getAllUserReport).then( data => {
                //Inserting user report to the DOM
                userReportTbody.innerHTML = all_user_report_html( data );

                //hide loading after fetching user report
                if( userReportLoading ){
                    userReportLoading.style.display = 'none';
                }
            } )
        }
    }

    //User preference products html
    function get_user_preference_html( data ){

        if( ! Array.isArray( data ) ){
            return;
        }

        if( data.length === 0 ){
            return '<h2 class="empty-preference-text">No Preference item</h2>';
        }

        let html = '<div class="tasty-preference-items-wrap">';

        data.forEach( item => {
            html += `<div class="tasty-preference-item">
                <img src="${item.featured_image}" class="tasty-item-image" alt="" />
                <h2>${item.title}</h2>
            </div>`;
        } );

        html += '</div>';

        return html;
    }

    //Tasty element specific product
    function tastyItemByElement( element, user = null ){
        const preference_tab_content = document.getElementById( 'preference-tab-content' );

        if( preference_tab_content ){

            preference_tab_content.innerHTML = 'Loading....';

            let param = `?element=${element}`;

            if( user ){
                param += `&user=${user}`;
            }

            fethcAdminApi( saveChoiceEndPoint + param ).then( data => {
                preference_tab_content.innerHTML = get_user_preference_html(data);
            } );
        }
        
    }

    //all preference buttons
    const elementsBtn  = document.querySelectorAll('.preference-tab-btn');
    const userDropowen = document.getElementById('preference-user');
    //initail element
    let element = 'sink';
    let user    = null;


    tastyItemByElement(element);

    if( elementsBtn ){
        elementsBtn.forEach( element => {
            
            element.addEventListener( 'click', function(){
                if( ! this.classList.contains("active-element") ){
                    element = this.getAttribute('data-element');
                    tastyItemByElement( element, user );
    
                    //active button functioinality
                    elementsBtn.forEach( btn => {
                        btn.classList.remove('active-element')
                    } );
    
                    this.classList.add('active-element');
                    
                }

            } )
        
        } )
    }

    //Update api on dropdown change
    if( userDropowen ){
        userDropowen.addEventListener('change', function(){
            user = this.value
            tastyItemByElement( element, user );
        })
    }

    /**
     * Test api
     */
    const performBtn   = document.querySelectorAll('.performance-tab-btn');
    const performUser  = document.getElementById('perform-user');
    //initail element
    let perform      = 'popularity';
    let performUserId = null;

    function tastyPerformanceIndicator( perform, user = null ){
        const perform_tab_content = document.getElementById( 'performance-tab-content' );

        if( perform_tab_content ){

            perform_tab_content.innerHTML = 'Loading....';

            let param = `?perform=${perform}`;

            if( user ){
                param += `&user=${user}`;
            }

            fethcAdminApi( tag_perform_EndPoint + param ).then( data => {
                perform_tab_content.innerHTML = popularityContentHtml(data);
            } );
        }
    }

    //initially run performance idicators
    tastyPerformanceIndicator(perform);

    if( performBtn ){
        performBtn.forEach( perform => {
            
            perform.addEventListener( 'click', function(){
                if( ! this.classList.contains("active-element") ){
                    perform = this.getAttribute('data-perform');
                    tastyPerformanceIndicator( perform, performUserId );
    
                    //active button functioinality
                    performBtn.forEach( btn => {
                        btn.classList.remove('active-element')
                    } );
    
                    this.classList.add('active-element');
                    
                }

            } )
        
        } )
    }
      
    //Update api on dropdown change
    if( performUser ){
        performUser.addEventListener('change', function(){
            performUserId = this.value
            tastyPerformanceIndicator( perform, performUserId );
        })
    }
      

    /**
     * Peform table head
     */
    function performTableHead( tableHeads ){
        if( ! Array.isArray(tableHeads) ){
            return;
        }

        console.log(tableHeads)

        let html = '<tr>';

        tableHeads.forEach( head => {
           let head_text = head.replace(/_/g, ' ');
            html += `<th>${head_text.charAt(0).toUpperCase() + head_text.slice(1).toLowerCase()}</th>`;
        } );

        html += '</tr>';

        return html;
    }

    /**
     * Popularity html
     */
    function popularityContentHtml( popularityData ){
        if( ! Array.isArray( popularityData ) ){
            return;
        }

        if( popularityData.length === 0 ){
            return '<h3>No data found</h3>';
        }

        const heads = Object.keys(popularityData[0]);

        let html = '<table class="tasty-user-report-table">';
        html += performTableHead( Object.keys(popularityData[0]) );

        popularityData.forEach( data => {
            html += '<tr>';
            heads.forEach( head => {
                html += `<td>${data[head]}</td>`;
            } );
            html += '</tr>'
        } );

        html += '</table>';

        return html;
    }


    //Fetch all user report
    append_user_report();
})(jQuery)