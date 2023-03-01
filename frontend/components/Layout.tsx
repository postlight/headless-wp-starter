import Header from './Header';
import Footer from './Footer';

const Layout = props => {
  const { children } = props;
  return (
    <div>
      <Header />
      <main>
        {children}
      </main>
      <Footer />
    </div>
  );
};

export default Layout;
