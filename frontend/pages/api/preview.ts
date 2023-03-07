import type { NextApiRequest, NextApiResponse } from 'next'
import Config from '../../config'

export default async function preview(
  req: NextApiRequest,
  res: NextApiResponse
) {
  const { id, rev, type, status, wpnonce } = req.query

  // Check the secret and next parameters
  // This secret should only be known by this API route
  if (
    // !process.env.WORDPRESS_PREVIEW_SECRET ||
    // secret !== process.env.WORDPRESS_PREVIEW_SECRET ||
    (!id || !wpnonce)
  ) {
    return res.status(401).json({ message: 'Invalid token' })
  }

  // checking if the post/page is a draft or a revision.
  let postUrl = `${Config.apiUrl}/wp/v2/${type}s/${id}/revisions/${rev}?_wpnonce=${wpnonce}`;
  if (status === 'draft') {
    postUrl = `${Config.apiUrl}/wp/v2/${type}s/${rev}?_wpnonce=${wpnonce}`;
  }

  const post = await fetch(
    postUrl,
    { credentials: 'include' }, // required for cookie nonce auth
  )
    .then((res) => res.json())
    .then((res) => res);

  console.log(postUrl);
  console.log(post);

  // If the post doesn't exist prevent preview mode from being enabled
  if (!post) {
    return res.status(401).json({ message: 'Post not found' })
  }

  // Enable Preview Mode by setting the cookies
  res.setPreviewData({
    post: {
      id: post.id,
      slug: post.slug,
      status: post.status,
    },
  })

  // Redirect to the path from the fetched post
  // We don't redirect to `req.query.slug` as that might lead to open redirect vulnerabilities
  res.writeHead(307, { Location: `/post/${post.slug}` })
  res.end()
}