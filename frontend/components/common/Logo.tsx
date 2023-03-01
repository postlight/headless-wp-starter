interface Props {
  width: number;
  height: number;
  color?: string;
}

export default function Logo({ width, height, color = "#000" }: Props) {
  return (
    <svg width={width} height={height} viewBox="0 0 214 142" version="1.1">
      <g id="logo/postlight/starter-kit" stroke="none" strokeWidth="1" fill={color} fillRule="evenodd">
        <path d="M213.066017,71 L142.355339,141.710678 L107,106.355339 L71.644661,141.710678 L0.933983,71 L71.644661,0.289322 L107,35.644661 L142.355339,0.289322 L213.066017,71 Z M142.355339,71 L107,35.644661 L71.644661,71 L107,106.355339 L142.355339,71 Z" id="Shape" fill={color} fillRule="nonzero"></path>
      </g>
    </svg>

  )
}