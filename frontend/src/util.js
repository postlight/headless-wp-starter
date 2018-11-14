import React from 'react';
import Link from "next/link";

const getSlug = (url) => {
  const parts = url.split("/");
  return parts.length > 2 ? parts[parts.length - 2] : "";
}

export const createLink = (item, index) => {
  if (item.object === "custom") {
    return (
      <Link href={item.url} key={item.ID}>
        <a>{item.title}</a>
      </Link>
    );
  }

  if (item.link) {
    return (
      <Link
        as={item.link}
        href={item.link}
        key={item.id}
      >
        <a>{item.title.rendered}</a>
      </Link>
    )
  }

  const slug = getSlug(item.url);
  const actualPage = item.object === "category" ? "category" : "post";
  return (
    <Link
      as={`/${item.object}/${slug}`}
      href={`/${actualPage}?slug=${slug}&apiRoute=${item.object}`}
      key={item.id}
    >
      <a>{item.title}</a>
    </Link>
  );
}