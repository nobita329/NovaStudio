import React, { createContext, useContext, useState } from 'react';

const FloatingContext = createContext<{
    floating: boolean;
    setFloating: (v: boolean) => void;
}>({
    floating: false,
    setFloating: () => undefined,
});

export const FloatingProvider: React.FC = ({ children }) => {
    const [floating, setFloating] = useState(false);
    // 4d86fa12c1e75b0191c6d12a366793f744e911346f727dc1091dab3aa0b043bf

    return <FloatingContext.Provider value={{ floating, setFloating }}>{children}</FloatingContext.Provider>;
};

export const useFloating = () => useContext(FloatingContext);
