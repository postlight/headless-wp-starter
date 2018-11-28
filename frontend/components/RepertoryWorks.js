import React from 'react'
import urlParse from 'url-parse'
import Link from "next/link";

const RepertoryWorks = ({repertoryWorks}) => {
  return (
    <div id="works-gallery">
      {repertoryWorks.map((work, i) =>
        <div className="work card m-2" key={i}>
          <img className="card-img-top" src={work['_embedded']['wp:featuredmedia'][0]['media_details']['sizes']['medium']['source_url']} alt={work.title.rendered} />
          <div className="card-body">
            <div className="work-name card-text">
              <Link
                prefetch
                as={`/current-repertory/${work.slug}/`}
                href={work.link}
              >
                <a>{work.title.rendered}</a>
              </Link>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default RepertoryWorks;
