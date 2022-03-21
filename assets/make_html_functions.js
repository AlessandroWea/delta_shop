export function make_comment_rating(rating)
{
    let html = '';

    if(rating !== 0)
    {
        for(let i = 0; i < rating; i++)
        {
            html += `<i class="fa fa-star"></i>`;
        }

        if(5 - rating > 0)
        {
            for(let i = 0; i < 5 - rating; i++)
            {
                html += `<i class="fa fa-star-o"></i>`;
            }
        }
    }
    else
    {
        for(let i = 0; i < 5; i++)
        {
            html += `<i class="fa fa-star-o"></i>`;
        }
    }

    return html;
}

export function make_comment_list(comments = [])
{
    let comment_list = '';

    comments.forEach(comment => {
        comment_list += `
            <li>
                <div class="review-heading">
                    <h5 class="name">${comment.name}</h5>
                    <p class="date">${comment.created}</p>
                    <div class="review-rating">`;

        comment_list += make_comment_rating(comment.rating);

        comment_list += `</div>
                </div>
                <div class="review-body">
                    <p>${comment.text}</p>
                </div>
            </li>
        `;
    });

    return comment_list;
}