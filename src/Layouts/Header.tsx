import React, { useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/router';
import styled, { DefaultTheme } from 'styled-components';
import Select from '@paljs/ui/Select';
import { LayoutHeader } from '@paljs/ui/Layout';
import { EvaIcon } from '@paljs/ui/Icon';
import { Button } from '@paljs/ui/Button';
import { Actions } from '@paljs/ui/Actions';
import ContextMenu from '@paljs/ui/ContextMenu';
import User from '@paljs/ui/User';
import { breakpointDown } from '@paljs/ui/breakpoints';
import CryptoJS from "crypto-js"
import Store from 'store'

const HeaderStyle = styled.div`
  display: flex;
  width: 100%;
  justify-content: space-between;
  ${breakpointDown('sm')`
    .right{
      display: none;
    }
  `}
  .right > div {
    height: auto;
    display: flex;
    align-content: center;
  }
  .logo {
    font-size: 1.25rem;
    white-space: nowrap;
    text-decoration: none;
  }
  .left {
    display: flex;
    align-items: center;
    .github {
      font-size: 18px;
      margin-right: 5px;
    }
  }
`;

const Label = styled.span`
  display: flex;
  align-items: center;
`;

const SelectStyled = styled(Select)`
  min-width: 150px;
`;

interface HeaderProps {
  toggleSidebar: () => void;
  theme: {
    set: (value: DefaultTheme['name']) => void;
    value: DefaultTheme['name'];
  };
  changeDir: () => void;
  dir: 'rtl' | 'ltr';
}

const Header: React.FC<HeaderProps> = (props) => {
  const router = useRouter();

  useEffect(() => {
    if (
      (
        !Store.get('auth-user') || (Store.get('auth-user') &&
        !CryptoJS.AES.decrypt(`${Store.get('auth-user')}`, `${process.env.NEXT_PUBLIC_SECRET_KEY}`).toString(CryptoJS.enc.Utf8))
      ) && router?.route !== '/dashboard'
    ) {
      window.location.href='/auth/login'
      return false
    }
  }, [])

  const themeOptions = () => [
    {
      value: 'default',
      label: (
        <Label>
          <EvaIcon name="droplet" options={{ fill: '#a6c1ff' }} />
          Default
        </Label>
      ),
    },
    {
      value: 'dark',
      label: (
        <Label>
          <EvaIcon name="droplet" options={{ fill: '#192038' }} />
          Dark
        </Label>
      ),
    },
    {
      value: 'cosmic',
      label: (
        <Label>
          <EvaIcon name="droplet" options={{ fill: '#5a37b8' }} />
          Cosmic
        </Label>
      ),
    },
    {
      value: 'corporate',
      label: (
        <Label>
          <EvaIcon name="droplet" options={{ fill: '#3366ff' }} />
          Corporate
        </Label>
      ),
      selected: true,
    },
  ];
  return (
    <LayoutHeader fixed>
      <HeaderStyle>
        <Actions
          size="Medium"
          actions={[
            {
              icon: { name: 'menu-2-outline' },
              url: {
                onClick: props.toggleSidebar,
              },
            },
            {
              content: (
                <Link href="/">
                  <a className="logo">Arisan Kita</a>
                </Link>
              ),
            },
            {
              content: (
                <SelectStyled
                  instanceId="react-select-input"
                  isSearchable={false}
                  shape="SemiRound"
                  placeholder="Themes"
                  value={themeOptions().find((item) => item.value === props.theme.value)}
                  options={themeOptions()}
                  onChange={({ value }: { value: DefaultTheme['name'] }) => props.theme.set(value)}
                />
              ),
            },
            // {
            //   content: (
            //     <Button size="Small" onClick={() => props.changeDir()}>
            //       {props.dir}
            //     </Button>
            //   ),
            // },
          ]}
        />
        <Actions
          size="Small"
          className="right"
          actions={[
            // {
            //   content: (
            //     <a
            //       className="left"
            //       href={`https://github.com/paljs/nextjs-admin-template`}
            //       target="_blank"
            //       rel="noreferrer"
            //     >
            //       <span className="github">Support us in GitHub</span>
            //       <img src={`https://badgen.net/github/stars/paljs/nextjs-admin-template`} />
            //     </a>
            //   ),
            // },
            // {
            //   content: (
            //     <a href="https://discord.gg/NRmdvDxsT8" target="_blank" rel="noreferrer">
            //       <img height="20" src="/discord.svg" alt="slack" />
            //     </a>
            //   ),
            // },
            // {
            //   icon: 'twitter',
            //   url: { href: 'https://twitter.com/AhmedElywh', target: '_blank' },
            // },
            {
              content: (
                (Store.get('auth-user')) ? <ContextMenu
                  nextJs
                  style={{ cursor: 'pointer' }}
                  placement="bottom"
                  currentPath={router.pathname}
                  items={[
                    { title: 'Profile', link: { href: '/modal-overlays/tooltip' } },
                    { title: 'Log out', link: { href: '/logout' } },
                  ]}
                  Link={Link}
                >
                  <User image="url('/icons/icon-72x72.png')" name="Ahmed Elywa" title="Manger" size="Medium" />
                </ContextMenu> : <Button status="Primary" onClick={() => router.push('/auth/login')} type="submit" shape="SemiRound">
                  Login / Register
                </Button>
              ),
            },
          ]}
        />
      </HeaderStyle>
    </LayoutHeader>
  );
};
export default Header;
