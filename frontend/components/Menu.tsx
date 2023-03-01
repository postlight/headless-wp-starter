import { FC, useEffect, useState } from 'react';
import Link from 'next/link';
import Router from 'next/router';
import Image from 'next/image';

import Logo from './common/Logo';
import Config from '../config';

interface Props {
  menu: any
}

const getSlug = (url) => {
  const parts = url.split('/');
  return parts.length > 2 ? parts[parts.length - 2] : '';
};

const Menu: FC<Props> = ({ menu }) => {
  const [auth, setAuth] = useState(null);

  useEffect(() => {
    const token = localStorage.getItem(Config.AUTH_TOKEN);
    const username = localStorage.getItem(Config.USERNAME);
    setAuth({ token, username });
  }, []);

  const handleSelectChange = (e) => {
    location.href = e.target.value;
  };

  return (
    <div className="menu bb">
      <div className="flex justify-between w-90-l center-l">
        <div className="brand bb flex justify-center items-center w-100 justify-between-l bn-l">
          <Link href="/" className="starter-kit-logo">
            <Logo width={48} height={32} />
            <div className="pl2">
              WordPress + React
              <br />
              Starter Kit
            </div>
          </Link>
        </div>
        <div className="links dn flex-l justify-between items-center">
          {menu.items.map((item) => {
            if (item.object === 'custom') {
              return (
                <a href={item.url} key={item.ID}>
                  {item.title}
                </a>
              );
            }
            const slug = getSlug(item.url);
            const actualPage =
              item.object === 'category' ? 'category' : 'post';
            return (
              <Link
                href={`/${actualPage}/${slug}`}
                key={item.ID}
              >
                {item.title}
              </Link>
            );
          })}

          <Link href="/search">
            <Image src="/images/search.svg" width={25} height={25} alt="Search icon" />
          </Link>

          {auth?.token ? (
            <a
              className="pointer round-btn ba bw1 pv2 ph3"
              onClick={() => {
                localStorage.removeItem(Config.AUTH_TOKEN);
                Router.push('/login');
              }}
            >
              Log out {auth.username}
            </a>
          ) : (
            <Link href="/login" className="round-btn ba bw1 pv2 ph3">
              Log in
            </Link>
          )}
        </div>
      </div>
      <div className="dropdown bb flex justify-center items-center dn-l">
        <select onChange={handleSelectChange}>
          <option>Menu</option>
          {menu.items.map((item) => {
            if (item.object === 'custom') {
              return (
                <option value={item.url} key={item.ID}>
                  {item.title}
                </option>
              );
            }
            const slug = getSlug(item.url);
            const actualPage =
              item.object === 'category' ? 'category' : 'post';
            return (
              <option
                value={`/${actualPage}?slug=${slug}&apiRoute=${item.object}`}
                key={item.ID}
              >
                {item.title}
              </option>
            );
          })}
        </select>
      </div>
    </div>
  );
}
export default Menu;
