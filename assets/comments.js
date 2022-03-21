
import * as mhf from './make_html_functions';
import {sendRequestJSON} from './request_functions';
import './styles/comments.css';

window.onload = () => {
    const comment_box = document.querySelector('.reviews');
    const more_comments_btn = document.querySelector('#more-comments-btn');

    let current_count_of_comments = 0;
    let product_id = document.querySelector('#product-id-holder').value;
    let total_count_of_comments = 0;

    sendRequestJSON('POST', '/get-comments-count', {'product-id' : product_id})
        .then(response => {
            if(response.count > 0)
            {
                total_count_of_comments = response.count;

                sendRequestJSON('POST', '/get-comments', {
                    'count' : current_count_of_comments,
                    'product-id' : product_id,
                }).then(response => {
                        comment_box.innerHTML = mhf.make_comment_list(response.comments);
                        current_count_of_comments = response.count;

                        if(current_count_of_comments >= total_count_of_comments)
                            more_comments_btn.style['display'] = 'none';
                    })

                more_comments_btn.addEventListener('click', function() {
                    sendRequestJSON('POST', '/get-comments', {
                        'count' : current_count_of_comments,
                        'product-id' : product_id,
                    }).then(response => {
                            comment_box.innerHTML += mhf.make_comment_list(response.comments);
                            current_count_of_comments = response.count;

                            if(current_count_of_comments >= total_count_of_comments)
                                more_comments_btn.style['display'] = 'none';
                        })
                });

            }
            else
            {
                more_comments_btn.style['display'] = 'none';
            }
        })
}