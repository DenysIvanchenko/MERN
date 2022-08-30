import React from 'react';
import {
Nav,
NavLink,
Bars,
NavMenu,
NavBtn,
NavBtnLink,
} from './NavbarElements';

const Navbar = () => {
return (
	<>
	<Nav>
		<Bars />

		<NavMenu>
		<NavLink to='/basic'>
			Basic
		</NavLink>
		<NavLink to='/rota'>
			Rota
		</NavLink>
		<NavLink to='/total'>
			Total
		</NavLink>
		</NavMenu>
		{/* <NavBtn>
		    <NavBtnLink to='/logout'>Logout</NavBtnLink>
		</NavBtn> */}
	</Nav>
	</>
);
};

export default Navbar;
